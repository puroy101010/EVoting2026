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
use App\Services\UtilityService;
use Dflydev\DotAccessData\Util;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OTPController extends Controller
{



    // done 2021-08-31

    public function store(SendOTPRequest $request)
    {

        try {

            $datetime   = EApp::datetime();
            $email      = $request->input('email');
            $accountNo  = $request->input('account_no');

            // Find user
            $userInfo = User::where('email', $email)
                ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                ->first();


            if ($userInfo === null) {

                Log::info("OTP: Email not found", ['email' => $email, 'accountNo' => $accountNo]);

                ActivityController::log(['activityCode' => '00006', 'accountNo' => $accountNo, 'email' => $email]);

                return response()->json(['message' => 'Account number or email is incorrect.'], 400);
            }

            Log::info("OTP: User info by email retrieved successfully.", ['email' => $email, 'accountNo' => $accountNo, 'userId' => $userInfo->id]);


            $accountInfo = $this->getUserInfo($userInfo, $email, $accountNo);


            if ($accountInfo === null) {

                Log::info("OTP: Account number not found", ['email' => $email, 'accountNo' => $accountNo]);

                ActivityController::log(['activityCode' => '00006', 'accountNo' => $accountNo, 'email' => $email]);

                return response()->json(['message' => 'Account number or email is incorrect.'], 400);
            }

            $otp =  $otp = $this->generateOTP();

            $authUserDetails = ["id" => $accountInfo->id, "email" => $email, "otp" => $otp];

            $this->sendOTP($email, $authUserDetails, $otp);

            DB::beginTransaction();

            $this->setOTP($accountInfo, $datetime, $authUserDetails, $otp);

            ActivityController::log(['activityCode' => '00005', 'accountNo' => $accountNo, 'email' => $email, 'data' => json_encode(['userId' => $accountInfo->id])]);

            DB::commit();

            return response()->json(['message' => 'OTP has been sent to your email.'], 200);
        } catch (Exception $e) {


            if (strpos($e->getMessage(), 'No Such User Here') !== false) {
                Log::error('OTP: The OTP could not be sent because the email address is not registered or does not exist.', [
                    "email" => $request->email,
                    'account_no' => $request->account_no
                ]);
                ActivityController::log(['activityCode' => '00126', 'accountNo' => $request->account_no, 'email' => $request->email]);
                return response()->json(['message' => 'Unable to send OTP. The email address is not registered or inactive.'], 400);
            }

            UtilityService::logServerError($request, $e, "Sending OTP failed");


            return response()->json([], 500);
        }
    }

    private function setOTP($accountInfo, $datetime, $authUserDetails, $otp)
    {
        $options = ['cost' => 12];
        $encOtp = password_hash($otp, PASSWORD_BCRYPT, $options);

        $updateOtp = User::where('id', $accountInfo->id)
            ->where('email', $accountInfo->email)
            ->update([
                'password' => $encOtp,
                'otpValid' => 1,
                'otpCreatedAt' => $datetime
            ]);

        if (!$updateOtp) {
            Log::error("OTP: Failed to update OTP in the database", $authUserDetails);
            return response()->json([], 500);
        }

        Log::info("OTP: OTP has been updated in the database", $authUserDetails);
    }

    private function sendOTP($email, $authUserDetails, $otp)
    {

        $subject = "ONE TIME PIN - Valley Golf and Country Club, Inc.";

        $otpEnabled = (int)ConfigService::getConfig('otp_login_enabled') === 1;

        Log::info("OTP: OTP login is " . ($otpEnabled ? "enabled" : "disabled"), $authUserDetails);

        if ($otpEnabled) {

            Log::info("OTP: Sending OTP to email {$email}", $authUserDetails);
            Mail::to($email)->send(new SendOtpMail($otp, $subject));
            Log::info("OTP: The OTP has been sent to email {$email}", $authUserDetails);
        }
    }

    private function generateOTP(): int
    {

        return rand(10000, 99999);
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

            $accountNo = $request->input('account_no');
            $email     = $request->input('email');
            $otp       = $request->input('otp');


            $userInfo = User::where('email', $email)
                ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                ->orderBy('id', 'asc')
                ->first();


            if ($userInfo === null) {
                Log::error("Verify OTP: User not found", ["email" => $email, "accountNo" => $accountNo, "otp" => $otp]);
                return response()->json([], 500);
            }

            $accountInfo = $this->getUserInfo($userInfo, $email, $accountNo);



            if (password_verify($otp, $accountInfo->password)) {

                Log::info("Verify OTP: OTP matched", ["email" => $email, "accountNo" => $accountNo, "otp" => $otp]);

                if ($accountInfo->otpValid !== 1) {

                    Log::warning("Verify OTP: OTP is invalid", ["email" => $email, "accountNo" => $accountNo, "otp" => $otp]);
                    return response()->json(['message' => 'The OTP you entered is incorrect.'], 400);
                }

                DB::beginTransaction();

                Auth::loginUsingId($accountInfo->id, FALSE);

                $accountInfo->otpValid = false;
                $accountInfo->save();

                ActivityController::log(['activityCode' => '00001', 'accountNo' => $accountNo, 'email' => $email, 'userId' => $accountInfo->id]);

                DB::commit();

                Log::info("Verify OTP: OTP verified successfully", ["email" => $email, "accountNo" => $accountNo, "otp" => $otp]);

                return response()->json(['message' => 'Success'], 200);
            }

            Log::info("Verify OTP: OTP did not match", ["email" => $email, "accountNo" => $accountNo, "otp" => $otp]);

            ActivityController::log(['activityCode' => '00007', 'accountNo' => $accountNo, 'email' => $email]);

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
