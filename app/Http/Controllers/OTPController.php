<?php

namespace App\Http\Controllers;

use App\Http\Requests\OverrideOtpRequest;
use App\Models\ActivityLog;
use App\Http\Requests\SendOTPRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\Request;
use \App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\User;
use App\Services\ConfigService;
use App\Services\OTPService;
use App\Services\UtilityService;
use Dflydev\DotAccessData\Util;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OTPController extends Controller
{


    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function store(SendOTPRequest $request)
    {

        return $this->otpService->generateAndStoreOTP($request);
    }







    private function getUserInfo($userInfo, $email, $accountNo): ?User
    {

        switch ($userInfo->role) {

            case 'stockholder':

                Log::info("OTP: User {$email} is trying to login as stockholder.");

                $accountInfo = User::where('email', $email)
                    ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                    ->whereHas('stockholder', function ($query) use ($accountNo) {
                        $query->where('accountNo', $accountNo);
                    })
                    ->orderBy('id', 'asc')
                    ->first();


                break;


            case 'corp-rep':

                Log::info("OTP: User {$email} is trying to login as corp-rep.");

                $accountInfo = User::where('email', $email)
                    ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                    ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                        $query->where('accountNo', $accountNo);
                    })
                    ->first();

                break;


            case 'non-member':

                Log::info("OTP: User {$email} is trying to login as non-member.");

                $accountInfo = User::where('email', $email)
                    ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                    ->whereHas('nonMemberAccount', function ($query) use ($accountNo) {
                        $query->where('nonmemberAccountNo', $accountNo);
                    })
                    ->first();

                break;

            default:
                Log::info("OTP: User {$email} is trying to login. Invalid user role.");
                throw new Exception("Unknown user role.");
                break;
        }

        Log::info("OTP: User info retrieved successfully.", ['email' => $email, 'accountNo' => $accountNo, 'userId' => $accountInfo->id ?? null]);

        return $accountInfo;
    }


    // done 2021-08-21
    public function verify(VerifyOtpRequest $request)
    {

        try {

            $email     = $request->input('email');
            $otp       = $request->input('otp');


            $userInfo = User::where('email', $email)
                ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                ->orderBy('id', 'asc')
                ->first();


            if ($userInfo === null) {
                Log::error("Verify OTP: User not found", ["email" => $email, "otp" => $otp]);
                return response()->json([], 500);
            }

            $accountInfo = $userInfo;

            // Check if OTP is expired (5 minutes = 300 seconds)
            if ($accountInfo->otpCreatedAt) {
                $createdAt = strtotime($accountInfo->otpCreatedAt);
                $now = strtotime(EApp::datetime());
                $timeDifference = $now - $createdAt;

                if ($timeDifference > 300) {
                    Log::warning("Verify OTP: OTP expired", ["email" => $email, "createdAt" => $accountInfo->otpCreatedAt, "timeDifference" => $timeDifference]);
                    ActivityController::log(['activityCode' => '00007', 'email' => $email]);
                    return response()->json(['message' => 'The OTP has expired. Please request a new OTP.'], 400);
                }
            }

            if (password_verify($otp, $accountInfo->password)) {

                Log::info("Verify OTP: OTP matched", ["email" => $email, "otp" => $otp]);

                if ($accountInfo->otpValid !== 1) {

                    Log::warning("Verify OTP: OTP is invalid", ["email" => $email, "otp" => $otp]);
                    return response()->json(['message' => 'The OTP you entered is incorrect.'], 400);
                }

                DB::beginTransaction();

                Auth::loginUsingId($accountInfo->id, FALSE);

                // Clear the OTP after successful verification
                $accountInfo->otpValid = false;
                $accountInfo->password = null;
                $accountInfo->otpCreatedAt = null;
                $accountInfo->save();

                ActivityController::log(['activityCode' => '00001', 'email' => $email, 'userId' => $accountInfo->id]);

                DB::commit();

                Log::info("Verify OTP: OTP verified successfully", ["email" => $email, "otp" => $otp]);

                return response()->json(['message' => 'Success'], 200);
            }

            Log::info("Verify OTP: OTP did not match", ["email" => $email, "otp" => $otp]);

            ActivityController::log(['activityCode' => '00007', 'email' => $email]);

            return response()->json(["message" => "The OTP you entered is incorrect"], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "OTP verification failed");

            return response()->json([], 500);
        }
    }



    public function login_details(Request $request)
    {

        try {

            $activityLog = ActivityLog::findOrFail($request->id);

            $accountNo = $activityLog->accountNo;

            $relatedAccounts = User::with('stockholder', 'stockholderAccount.stockholder.user', 'nonMemberAccount')->where('email', $activityLog->email)
                ->where(function ($query) use ($accountNo) {
                    $query->whereHas('stockholder', function ($subquery) use ($accountNo) {
                        $subquery->orWhere('accountNo', $accountNo);
                    })
                        ->orWhereDoesntHave('stockholder'); // Include records with no related stockholder
                })
                ->get();




            return $relatedAccounts;
        } catch (Exception $e) {

            return response()->json([], 400);
        }
    }

    public function override(OverrideOtpRequest $request)
    {

        try {

            Log::info("OTP: OTP override requested", ["logId" => $request->id]);
            $loginDetails = ActivityLog::findOrFail($request->id);

            $otp = rand(10000, 99999);
            $options = ['cost' => 12];

            $encOtp = password_hash($otp, PASSWORD_BCRYPT, $options);


            $accountNo = $loginDetails->accountNo;
            $email = $loginDetails->email;

            $decodedActivityData = json_decode($loginDetails->data ?? '{}');

            $userIdToOverride = $decodedActivityData->userId ?? null;

            if ($userIdToOverride === null) {
                Log::error("OTP: OTP override failed. User ID to override not found in activity log data.", ["logId" => $request->id, "email" => $email, "accountNo" => $accountNo]);
                return response()->json(['message' => 'User ID to override not found.'], 400);
            }


            $userInfo = User::findOrFail($userIdToOverride);

            if ($userInfo->otpValid === 0) {

                Log::warning("OTP: OTP override failed. OTP is not valid or already used.", ["email" => $email, "accountNo" => $accountNo, "otpValid" => $userInfo->otpValid]);

                return response()->json(['message' => 'User already logged in.'], 400);
            }

            DB::beginTransaction();

            $userInfo->password = $encOtp;
            $userInfo->otpCreatedAt = EApp::datetime();
            $userInfo->otpValid = true;
            $userInfo->save();

            ActivityController::log(['activityCode' => '00043', 'accountNo' => $loginDetails->accountNo, 'email' => $loginDetails->email, 'userId' => $userInfo->first()->id]);
            DB::commit();
            Log::info("OTP: OTP overridden successfully", ["email" => $email, "accountNo" => $accountNo, "otp" => $otp]);
            return response()->json(['message' => 'OTP overridden successfully. OTP: ' . $otp], 200);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "OTP override failed");
            return response()->json([], 500);
        }
    }
}
