<?php

namespace App\Http\Controllers;

use App\Http\Requests\OverrideOtpRequest;
use App\Models\ActivityLog;
use App\Http\Requests\SendOTPRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\User;
use Appp\Services\UserService;
use App\Services\OTPService;
use App\Services\UtilityService;

use Illuminate\Support\Facades\Log;


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




    public function verify(VerifyOtpRequest $request)
    {
        return $this->otpService->verify($request);
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
