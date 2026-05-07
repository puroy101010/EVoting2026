<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Http\Requests\DestroyStockholderDateSettingRequest;
use App\Http\Requests\IndexSettingRequest;
use App\Http\Requests\ToggleAmendmentModuleRequest;
use App\Http\Requests\ToggleBodModuleRequest;
use App\Http\Requests\ToggleOtpLoginRequest;
use App\Http\Requests\ToggleVotingReceiptRequest;
use App\Http\Requests\UpdateStockholderDateSettingRequest;
use App\Http\Requests\UpdateTermsAndConditionsRequest;
use App\Http\Requests\UpdateVotesPerShareSettingRequest;
use App\Services\UtilityService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{

    // semi complete
    public function index(IndexSettingRequest $request)
    {

        try {

            Log::info('Viewed settings page');
            return view('admin.settings.settings', [
                "config" => AppController::app_setting(),
            ]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while fetching settings');

            return view('errors.response', ['code' => 500, 'message' => 'Error loading settings page']);
        }
    }

    public static function format_date($dateTime)
    {

        return $dateTime === null ? '' : date('Y-m-d\TH:i', strtotime($dateTime));
    }




    private function updateConfigurationAndLog($fieldStart, $start, $fieldEnd, $end, $code)
    {


        $formattedStartDateTime = $start->format('h:i A');
        $formattedStartDate = $start->format('F d, Y');
        $formattedEndDateTime = $end->format('h:i A');
        $formattedEndDate = $end->format('F d, Y');

        $messageTemplate = "Configured %s schedule from {$formattedStartDate} {$formattedStartDateTime} to {$formattedEndDate} {$formattedEndDateTime}.";

        Configuration::where('config', $fieldStart)->update(['value' => $start]);
        Configuration::where('config', $fieldEnd)->update(['value' => $end]);

        $logMessages = [
            '00049' => 'Stockholder Online Voting',
            '00050' => 'Proxy Voting',
        ];

        $log = ['activityCode' => $code, 'remarks' => sprintf($messageTemplate, $logMessages[$code])];

        ActivityController::log($log);
    }




    public function update(UpdateStockholderDateSettingRequest $request)
    {
        try {


            $dateTimeStart = Carbon::parse($request->input('start_date_time'));
            $dateTimeEnd = Carbon::parse($request->input('end_date_time'));

            if ($dateTimeEnd <= $dateTimeStart) {

                Log::warning("Invalid date range provided for voting settings update", [
                    'start_date_time' => $dateTimeStart,
                    'end_date_time' => $dateTimeEnd
                ]);

                return response()->json(['message' => 'End date must be after start date'], 422);
            }



            DB::beginTransaction();

            switch ($request->input('form')) {
                case 'vote_in_person':

                    $this->updateConfigurationAndLog('vote_in_person_start', $dateTimeStart, 'vote_in_person_end', $dateTimeEnd, '00049');
                    break;

                case 'vote_by_proxy':
                    $this->updateConfigurationAndLog('vote_by_proxy_start', $dateTimeStart, 'vote_by_proxy_end', $dateTimeEnd, '00050');
                    break;
            }

            DB::commit();

            return response()->json(['message' => 'The date set for voting has been updated successfully.'], 200);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while updating voting date settings');
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }


    // semi complete
    public function destroy(DestroyStockholderDateSettingRequest $request)
    {

        try {


            Log::info('Removing voting date settings', ['form' => $request->input('form')]);
            DB::beginTransaction();

            $formMap = [
                'vote_in_person' => 'Stockholder Online Voting',
                'vote_by_proxy' => 'Proxy Voting    '
            ];


            switch ($request->input('form')) {

                case 'vote_in_person':

                    $updateStart = Configuration::where('config', 'vote_in_person_start')->update(['value' => null]);
                    $updateEnd = Configuration::where('config', 'vote_in_person_end')->update(['value' => null]);

                    $code = '00051';


                    if ($updateStart !== 1 || $updateEnd !== 1) {

                        return response()->json(['message' => '00051'], 500);
                    }


                    break;

                case 'vote_by_proxy':
                    $updateStart = Configuration::where('config', 'vote_by_proxy_start')->update(['value' => null]);
                    $updateEnd = Configuration::where('config', 'vote_by_proxy_end')->update(['value' => null]);

                    $code = '00052';


                    if ($updateStart !== 1 || $updateEnd !== 1) {

                        return response()->json(['message' => '00052'], 500);
                    }

                    break;

                default:

                    throw new Exception("An error occurred while attempting to remove the date set for voting. ");
            }


            if ($updateStart !== 1 || $updateEnd !== 1) {

                return response()->json([], 500);
            }


            ActivityController::log([
                'activityCode' => $code,
                'remarks' => 'Removed ' . $formMap[$request->input('form')] . ' period configuration. Voting dates have been cleared and need to be reconfigured.'
            ]);

            DB::commit();

            return response()->json(['message' => 'The date set for voting has been removed successfully.'], 200);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while removing voting date settings');
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    public function updateVotesPerShare(UpdateVotesPerShareSettingRequest $request)
    {
        try {



            DB::beginTransaction();

            $votesPerShare = $request->input('votes_per_share');

            // Update or create the votes per share configuration
            $config = Configuration::where('config', 'votes_per_share')->firstOrFail();

            $config->update(['value' => $votesPerShare]);


            // Log the activity
            ActivityController::log([
                'activityCode' => '00117',
                'remarks' => sprintf('Updated votes per share setting to %d', $votesPerShare)
            ]);



            DB::commit();

            return response()->json([
                'message' => 'Votes per share updated successfully',
                'votes_per_share' => $votesPerShare
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error updating votes per share: ' . $e->getMessage());
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    public function toggleAmendmentModule(ToggleAmendmentModuleRequest $request)
    {
        try {

            DB::beginTransaction();

            $enabled = $request->boolean('enabled');




            // Update or create the amendment module configuration
            $config = Configuration::where('config', 'amendment_enabled')->firstOrFail();
            $config->value = $enabled ? 1 : 0;


            Log::debug('Request', ['enabled' => $enabled]);
            Log::debug('To set', ['enabled' => $enabled ? 1 : 0]);
            Log::debug('Current', ['enabled' => $config->value]);


            if (!$config->isDirty()) {
                Log::info('No changes made to the amendment module setting', ['enabled' => $enabled]);
                return response()->json(['message' => 'No changes made to the amendment module setting'], 422);
            }

            $config->save();

            $activityCode = $enabled ? '00117' : '00118'; // Amendment module enabled/disabled
            ActivityController::log(['activityCode' => $activityCode]);

            DB::commit();

            Log::info('Amendment module setting updated', [
                'enabled' => $enabled,
                'activity_code' => $activityCode
            ]);

            $status = $enabled ? 'enabled' : 'disabled';
            return response()->json([
                'message' => "Amendment module {$status} successfully",
                'enabled' => $enabled
            ], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while toggling amendment module');
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    public function toggleBodModule(ToggleBodModuleRequest $request)
    {
        try {

            DB::beginTransaction();

            $enabled = $request->boolean('enabled');

            // Update or create the Board of Director module configuration
            $config = Configuration::where('config', 'bod_module_enabled')->firstOrFail();
            $config->value = $enabled ? 1 : 0;

            if (!$config->isDirty()) {
                return response()->json(['message' => 'No changes made to the Board of Director module setting'], 422);
            }

            $config->save();

            $activityCode = $enabled ? '00141' : '00142'; // Board of Director module enabled/disabled
            ActivityController::log(['activityCode' => $activityCode]);

            DB::commit();

            $status = $enabled ? 'enabled' : 'disabled';
            return response()->json([
                'message' => "Board of Director module {$status} successfully",
                'enabled' => $enabled
            ], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while toggling Board of Director module');
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    public function toggleOtpLogin(ToggleOtpLoginRequest $request)
    {
        try {

            DB::beginTransaction();

            $enabled = $request->boolean('enabled');

            // Update or create the amendment module configuration
            $config = Configuration::where('config', 'otp_login_enabled')->firstOrFail();
            $config->value = $enabled  ? 1 : 0;

            if (!$config->isDirty()) {
                Log::info('No changes made to the OTP login setting', ['enabled' => $enabled]);
                return response()->json(['message' => 'No changes made to the OTP login setting'], 422);
            }

            $config->save();

            $activityCode = $enabled ? '00122' : '00123'; // OTP login enabled/disabled
            ActivityController::log(['activityCode' => $activityCode]);

            DB::commit();

            Log::info('OTP login setting updated', [
                'enabled' => $enabled,
                'activity_code' => $activityCode
            ]);

            $status = $enabled ? 'enabled' : 'disabled';
            return response()->json([
                'message' => "OTP login {$status} successfully",
                'enabled' => $enabled
            ], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while toggling OTP login');
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }


    public function toggleVotingReceipt(ToggleVotingReceiptRequest $request)
    {
        try {

            DB::beginTransaction();

            $enabled = $request->boolean('enabled');

            // Update or create the amendment module configuration
            $config = Configuration::where('config', 'send_voting_confirmation_receipt_enabled')->firstOrFail();
            $config->value = $enabled  ? 1 : 0;

            if (!$config->isDirty()) {
                Log::info('No changes made to the email vote confirmation setting', ['enabled' => $enabled]);
                return response()->json(['message' => 'No changes made to the email vote confirmation setting'], 422);
            }

            $config->save();

            $activityCode = $enabled ? '00124' : '00125'; // Email vote confirmation enabled/disabled
            ActivityController::log(['activityCode' => $activityCode]);

            DB::commit();

            Log::info('Email vote confirmation setting updated', [
                'enabled' => $enabled,
                'activity_code' => $activityCode
            ]);

            $status = $enabled ? 'enabled' : 'disabled';
            return response()->json([
                'message' => "Email vote confirmation {$status} successfully",
                'enabled' => $enabled
            ], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while toggling email vote confirmation');
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    public function updateTermsAndConditions(UpdateTermsAndConditionsRequest $request)
    {


        try {

            Log::info('Updating terms and conditions', ['request' => $request->all()]);


            DB::beginTransaction();

            $type = $request->input('type') === 'online' ? 'terms_and_conditions_online' : 'terms_and_conditions_proxy';
            $content = $request->input('content');



            if (stripos($content, '[voter_name]') === false) {
                Log::warning('Terms and conditions content missing [voter_name] placeholder', ['type' => $type]);
                return response()->json(['message' => 'The terms and conditions must include the [voter_name] placeholder.'], 422);
            }


            // Update the terms and conditions in the database

            $termsAndConditions = Configuration::where('config', $type)->firstOrFail();
            $originalContent = $termsAndConditions->value;
            $termsAndConditions->value = $content;

            if (!$termsAndConditions->isDirty()) {
                Log::info('No changes made to the terms and conditions', ['type' => $type]);
                return response()->json(['message' => 'No changes made to the terms and conditions'], 422);
            }


            $termsAndConditions->save();

            ActivityController::log([
                'activityCode' => $request->input('type') === 'online' ? '00133' : '00134',
                'remarks' => 'Updated terms and conditions for ' . ($request->input('type') === 'online' ? 'Stockholder Online Voting' : 'Proxy Voting'),
                'data' => json_encode(['content' => [
                    'old' => $originalContent,
                    'new' => $content
                ]])
            ]);

            DB::commit();

            Log::info('Terms and conditions updated successfully', ['type' => $type, 'content' => $content]);

            return response()->json(['message' => 'Terms and conditions updated successfully'], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while updating terms and conditions');
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    public function toggleAmendmentRestriction(Request $request)
    {
        try {

            DB::beginTransaction();

            $enabled = $request->boolean('enabled');

            // Update or create the amendment restriction configuration
            $config = Configuration::where('config', 'amendment_restricted_to_gm')->firstOrFail();
            $config->value = $enabled  ? 1 : 0;

            if (!$config->isDirty()) {
                Log::info('No changes made to the amendment restriction setting', ['enabled' => $enabled]);
                return response()->json(['message' => 'No changes made to the amendment restriction setting'], 422);
            }

            $config->save();

            $activityCode = $enabled ? '00143' : '00144'; // Amendment restriction enabled/disabled
            ActivityController::log([
                'activityCode' => $activityCode,
                'remarks' => $enabled
                    ? 'Enabled amendment restriction to general manager only'
                    : 'Disabled amendment restriction to general manager only'
            ]);

            DB::commit();

            Log::info('Amendment restriction setting updated', [
                'enabled' => $enabled,
                'activity_code' => $activityCode
            ]);

            $status = $enabled ? 'enabled' : 'disabled';
            return response()->json([
                'message' => "Amendment restriction {$status} successfully",
                'enabled' => $enabled
            ], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while toggling amendment restriction');
            return response()->json(['message' => 'An error occurred while toggling amendment restriction'], 500);
        }
    }
}
