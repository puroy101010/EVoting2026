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

            $userInfo = User::where('email', $email)
                ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                ->first();

            if ($userInfo === null) {
                Log::info("OTP: Email not found", ['email' => $email]);
                ActivityController::log(['activityCode' => '00006', 'email' => $email]);

                return response()->json(['message' => 'Unable to send OTP. The email address is not registered or inactive.'], 404);
            }

            Log::info("OTP: User info by email retrieved successfully.", ['email' => $email, 'userId' => $userInfo->id]);


            // Calculate expiry time (5 minutes from creation)
            $expiryTime = date('Y-m-d H:i:s', strtotime($datetime) + 300);


            // Rate limit: do not resend within 5 minutes.
            if (!empty($userInfo->otpCreatedAt)) {
                $createdAt = strtotime($userInfo->otpCreatedAt);
                $now = strtotime($datetime);
                $elapsedSeconds = $now - $createdAt;

                if ($elapsedSeconds < self::OTP_RESEND_WAIT_SECONDS) {
                    $waitTime = self::OTP_RESEND_WAIT_SECONDS - $elapsedSeconds;
                    $formattedWaitTime = $this->convertSecondsToMinutesAndSeconds($waitTime);
                    Log::info("OTP: Resend request too soon", ['email' => $email, 'waitTime' => $formattedWaitTime]);
                    ActivityController::log(['activityCode' => '00147', 'email' => $email]);
                    return response()->json([
                        'message' => "Please wait {$formattedWaitTime} before requesting another OTP.",
                        'otpCreatedAt' => $userInfo->otpCreatedAt,
                        'otpExpiresAt' => date('Y-m-d H:i:s', strtotime($userInfo->otpCreatedAt) + 300),
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
                'email' => $email,
                'data' => json_encode(['userId' => $userInfo->id])
            ]);


            //$this->sendOTP($email, $authUserDetails, $otp);





            DB::commit();

            return response()->json([
                'message' => 'OTP has been sent to your email.',
                'otpCreatedAt' => $datetime,
                'otpExpiresAt' => $expiryTime
            ], 200);
        } catch (Exception $e) {
            try {
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
            } catch (Exception $rollbackException) {
                // ignore rollback failures
            }

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
}
