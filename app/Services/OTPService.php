<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EApp;
use App\Models\NonMemberAccount;
use App\Models\ProxyAmendment;
use App\Models\ProxyAmendmentHistory;
use App\Models\ProxyBoardOfDirector;
use App\Models\ProxyBoardOfDirectorHistory;
use App\Models\StockholderAccount;
use App\Models\User;
use App\Stockholder;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


use App\Http\Requests\OverrideOtpRequest;
use App\Models\ActivityLog;
use App\Http\Requests\SendOTPRequest;
use App\Http\Requests\VerifyOtpRequest;
use \App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Validator;
use App\Services\ConfigService;
use App\Services\UtilityService;
use DateTime;
use Dflydev\DotAccessData\Util;
use Illuminate\Support\Facades\Mail;

class OTPService
{


    const OTP_RESEND_WAIT_SECONDS = 300; // 5 minutes

    public function generateAndStoreOTP(Request $request)
    {
        $datetime = EApp::datetime();
        $email = $request->validated()['email'];

        try {

            $userInfo = UserService::findUserByEmail($email);

            if ($userInfo === null) {
                Log::info("OTP: Email not found", ['email' => $email]);
                ActivityController::log(['activityCode' => '00006', 'email' => $email]);

                return response()->json(['message' => 'Unable to send OTP. The email address is not registered or inactive.'], 404);
            }

            Log::info("OTP: User info by email retrieved successfully.", ['email' => $email, 'userId' => $userInfo->id]);


            // Rate limit: do not resend within 5 minutes.
            if (!empty($userInfo->otpCreatedAt)) {
                $createdAt = strtotime($userInfo->otpCreatedAt);
                $now = strtotime($datetime);
                $elapsedSeconds = $now - $createdAt;

                if ($elapsedSeconds < self::OTP_RESEND_WAIT_SECONDS) {
                    $waitTime = self::OTP_RESEND_WAIT_SECONDS - $elapsedSeconds;
                    $formattedWaitTime = $this->convertSecondsToMinutesAndSeconds($waitTime);
                    Log::info("OTP: Resend request too soon", ['email' => $email, 'waitTime' => $formattedWaitTime]);
                    ActivityController::log(['activityCode' => '00147', 'email' => $email, 'remarks' => json_encode(['waitTime' => $formattedWaitTime])]);
                    return response()->json([
                        'message' => "Please wait before requesting a new OTP.",
                        'otpCreatedAt' => $userInfo->otpCreatedAt,
                        'otpExpiresAt' => date('Y-m-d H:i:s', strtotime($userInfo->otpCreatedAt) + self::OTP_RESEND_WAIT_SECONDS),
                        'waitTime' => $waitTime
                    ], 429);
                }
            }

            $otp = $this->generateOTP();

            $authUserDetails = [
                "id" => $userInfo->id,
                "email" => $email,
                "otp" => $otp
            ];

            // Persist OTP first; then send email. This avoids race where email sends but DB fails.
            DB::beginTransaction();
            $this->setOTP($userInfo, $datetime, $authUserDetails, $otp);
            ActivityController::log([
                'activityCode' => '00005',
                'accountNo' => $userInfo->account_no,
                'userId' => $userInfo->id,
                'email' => $email,
                'data' => json_encode(['userId' => $userInfo->id])
            ]);

            if (app()->isProduction()) {
                $this->sendOTP($email, $authUserDetails, $otp);
            }

            DB::commit();

            return response()->json([
                'message' => 'OTP has been sent to your email.',
                'otpCreatedAt' => $datetime,
                'otpExpiresAt' => date('Y-m-d H:i:s', strtotime($datetime) + self::OTP_RESEND_WAIT_SECONDS)
            ], 200);
        } catch (Exception $e) {

            DB::rollback();

            if (strpos($e->getMessage(), 'No Such User Here') !== false) {
                Log::error('OTP: The OTP could not be sent because the email address is not registered or does not exist.', [
                    "email" => $request->email
                ]);
                ActivityController::log(['activityCode' => '00126', 'email' => $request->email]);
                return response()->json(['message' => 'Unable to send OTP. The email address is not registered or inactive.'], 400);
            }

            UtilityService::logServerError($request, $e, "Sending OTP failed");
            return response()->json([], 500);
        }
    }

    //convert seconds to minutes and seconds format
    private function convertSecondsToMinutesAndSeconds(int $seconds): string
    {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return sprintf("%02d:%02d", $minutes, $remainingSeconds);
    }


    /**
     * Generate a random 5-digit OTP. The OTP is generated as an integer to avoid issues with leading zeros.
     * @return int
     * @throws Exception
     */
    private function generateOTP(): int
    {
        return rand(10000, 99999);
    }

    /**
     * Send the OTP to the user's email.
     * @param string $email
     * @param array $authUserDetails
     * @param int $otp
     */
    private function sendOTP(string $email, array $authUserDetails, int $otp)
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

    /**
     * Set the OTP for the user in the database.
     * @param User $accountInfo
     * @param string $datetime
     * @param array $authUserDetails
     * @param int $otp
     */
    private function setOTP(User $accountInfo, string $datetime, array $authUserDetails, int $otp)
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




    public function verify(VerifyOtpRequest $request)
    {
        try {
            $email = $request->input('email');
            $otp = $request->input('otp');

            $userInfo = UserService::findUserByEmail($email);

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

                if ($timeDifference > self::OTP_RESEND_WAIT_SECONDS) {
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
}
