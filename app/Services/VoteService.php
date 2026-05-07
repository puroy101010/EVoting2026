<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Mail\VoteSuccessMail;
use App\Models\Agenda;
use App\Models\ProxyAmendment;
use App\Models\ProxyBoardOfDirector;
use App\Models\User;
use Carbon\Carbon;
use Exception;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;
use App\Models\Amendment;
use App\Models\Attendance;
use App\Models\Ballot;
use App\Models\BallotAgenda;
use App\Models\BallotAmendment;
use App\Models\BallotConfirmation;
use App\Models\BallotDetail;
use App\Models\Candidate;
use App\Models\Stockholder;
use App\Models\StockholderAccount;
use App\Models\UsedAmendmentAccount;
use App\Models\UsedBoardOfDirectorAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use PSpell\Config;

class VoteService
{

    protected $stockholderOnlineActive = false;
    protected $proxyVotingActive = false;


    public function index(Request $request)
    {
        $user = Auth::user();

        Log::info("Voting Page: Loading page");

        Log::info("Voting Page: Getting account IDs for BOD");
        $accountIdsBod = $this->getAccountIds('bod', true);

        Log::info("Voting Page: Getting account IDs for Amendment");
        $accountIdsAmendment = $this->getAccountIds('amendment', true);

        Log::info("Voting Page: Fetching BOD proxies");
        $proxyBod = ProxyBoardOfDirector::whereIn('accountId', $accountIdsBod)->get()->toArray();

        Log::info("Voting Page: Fetching Amendment proxies");
        $proxyAmendment = ProxyAmendment::whereIn('accountId', $accountIdsAmendment)->get()->toArray();

        Log::info("Voting Page: Formatting revoke options");
        $revokeOptions = $this->formatRevokeOptions($proxyBod, $proxyAmendment);

        Log::info("Voting Page: Getting display role for user");
        $accountRole = UtilityService::getDisplayUserRole($user);

        $onlineVoting = $this->checkVotingDay('Stockholder Online Voting');
        $proxyVoting = $this->checkVotingDay('Proxy Voting');

        $settings = ConfigService::getConfig();

        $stockholderOnlineTC = $settings['terms_and_conditions_online'] ?? null;
        $proxyVotingTC = $settings['terms_and_conditions_proxy'] ?? null;

        $fullName = Auth::user()->full_name;

        $stockholderOnlineTC = str_ireplace('[voter_name]', $fullName, $stockholderOnlineTC);
        $proxyVotingTC = str_ireplace('[voter_name]', $fullName, $proxyVotingTC);

        $issuedProxy = count($proxyBod) + count($proxyAmendment) > 0;

        $param = array_merge([
            'role' => $accountRole,
            "accountRole" => $accountRole,
            'btnDisableOnlineVoting' => $this->stockholderOnlineActive == true ? '' : 'disabled',
            'btnDisableProxyVoting' => $this->proxyVotingActive == true ? '' : 'disabled',
            'stockholderOnlineTT' => $onlineVoting,
            'proxyVotingTT' => $proxyVoting,
            'accountIdsBod' => $accountIdsBod,
            'accountIdsAmendment' => $accountIdsAmendment,
            'stockholderOnlineTC' => $stockholderOnlineTC,
            'proxyVotingTC' => $proxyVotingTC,
            'issuedProxy' => $issuedProxy,
            'userInitials' => $this->generateUserInitials($user),
            'amendmentEnabled' => (int) $settings['amendment_enabled'] === 1,
            'boardOfDirectorEnabled' => (int) $settings['bod_module_enabled'] === 1

        ], $revokeOptions);

        Log::info("Voting Page: Successfully loaded", ["data" => $param]);

        ActivityController::log([
            'activityCode' => '00132',
            'remarks' => 'Accessed voting page',
            'data' => json_encode($param),
            'userId' => Auth::user()->id
        ]);

        return view('user.voting-page', $param);
    }

    private function generateUserInitials(User $user): string
    {

        // Generate user initials for avatar
        $userFullName = $user->full_name ?? '';
        $nameWords = explode(' ', trim($userFullName));
        $userInitials = '';
        foreach ($nameWords as $word) {
            if (!empty($word)) {
                $userInitials .= strtoupper(substr($word, 0, 1));
            }
        }
        $userInitials = substr($userInitials, 0, 2); // Limit to 2 characters
        return $userInitials;
    }







    private function formatRevokeOptions(array $proxyBod, array $proxyAmendment): array
    {

        $amendment = $bod = $all = $none = false;

        $hasAmendmentProxy = count($proxyAmendment) > 0;
        $hasBodProxy = count($proxyBod) > 0;


        if ($hasAmendmentProxy || $hasBodProxy) {
            Log::info('Voting Page: User has either existing BOD, amendment proxies, or both, enabling the "None" revoke option.');
            $none = true; // Always enable "none" if user has any proxy
        }


        if ($hasAmendmentProxy) {
            Log::info('Voting Page: User has existing amendment proxies, enabling "Amendment Only" revoke option.');
            $amendment = true;
        }

        // Enable BOD option if user has BOD proxy
        if ($hasBodProxy) {
            Log::info('Voting Page: User has existing BOD proxies, enabling "Board of Director Only" revoke option.');
            $bod = true;
        }

        // Enable "all/both" option only if user has both types of proxies
        if ($hasAmendmentProxy && $hasBodProxy) {
            Log::info('Voting Page: User has existing BOD and amendment proxies, enabling "All" revoke option.');
            $all = true;
        }

        return [
            'amendment' => $amendment,
            'bod' => $bod,
            'all' => $all,
            'none' => $none
        ];
    }


    /**
     * Get account IDs based on proxy type and usage status.
     * @param string $proxyType Type of proxy ('bod' or 'amendment')
     * @param bool $unusedOnly Whether to fetch only unused accounts
     * @return array List of account IDs
     * @throws Exception If an invalid proxy type is provided
     * @throws \Illuminate\Database\QueryException If a database query error occurs
     * @throws \Exception For any other exceptions
     * 
     * Called by: VoteController@index
     */
    private function getAccountIds(string $proxyType, bool $unusedOnly = true): array
    {
        switch (Auth::user()->role) {
            case 'stockholder':
                $assignorAccountIds = $this->getAccountsForStockholder($proxyType, $unusedOnly);
                break;

            case 'corp-rep':
                $assignorAccountIds = $this->getAccountsForCorpRep($proxyType, $unusedOnly);
                break;
            default:
                $assignorAccountIds = [];
        }

        return $assignorAccountIds;
    }

    private function getAccountsForCorpRep(string $proxyType, bool $unusedOnly = true)
    {

        $relatedModel = UtilityService::validateProxyType($proxyType);

        Log::info("Voting Page: Fetching account IDs for corporate representative", [
            'proxyType' => $proxyType,
            'unusedOnly' => $unusedOnly
        ]);

        $accountIds = [];
        $corpRepAccountQuery = StockholderAccount::leftJoin('users', 'users.id', '=', 'stockholder_accounts.userId')
            ->leftJoin('stockholders', 'stockholders.stockholderId', '=', 'stockholder_accounts.stockholderId')
            ->where('users.email', Auth::user()->email)
            ->where('stockholders.accountNo', Auth::user()->stockholderAccount->stockholder->accountNo)
            ->where('users.role', 'corp-rep')
            ->where('stockholder_accounts.isDelinquent', 0);

        if ($unusedOnly) {
            $corpRepAccountQuery->whereDoesntHave($relatedModel);
        }

        $corpRepAccounts = $corpRepAccountQuery->get();


        foreach ($corpRepAccounts as $corpRepAccount) {

            array_push($accountIds, $corpRepAccount->accountId);
        }

        Log::info("Voting Page: Fetched account IDs for corporate representative", [
            'accountIds' => $accountIds,
            'proxyType' => $proxyType,
            'unusedOnly' => $unusedOnly
        ]);
        return $accountIds;
    }

    private function getAccountsForStockholder(string $proxyType, bool $unusedOnly = true): array
    {

        $relatedModel = UtilityService::validateProxyType($proxyType);

        Log::info("Voting Page: Fetching account IDs for stockholder", [
            'proxyType' => $proxyType,
            'unusedOnly' => $unusedOnly
        ]);

        $accountIds = [];


        $stockholderAccountsQuery = StockholderAccount::where('stockholderId', Auth::user()->stockholder->stockholderId)
            ->where('isDelinquent', 0);


        if ($unusedOnly) {
            $stockholderAccountsQuery->whereDoesntHave($relatedModel);
        }

        $stockholderAccounts = $stockholderAccountsQuery->get();

        foreach ($stockholderAccounts as $account) {
            $accountIds[] = $account->accountId;
        }

        Log::info("Voting Page: Fetched account IDs for stockholder", [
            'account_ids' => $accountIds,
            'proxyType' => $proxyType,
            'unusedOnly' => $unusedOnly
        ]);

        return $accountIds;
    }






    /**
     * Check if stockholder online voting is currently available
     * 
     * @return bool|string Returns true if voting is available, error message string otherwise
     * Called by: VoteController@index
     */
    private function checkVotingDay($votingType)
    {

        $votingType = UtilityService::validateVotingType($votingType);

        $currentDateTime = Carbon::now();
        $settings = ConfigService::getConfig();

        $startDateTime = $votingType === 'Stockholder Online Voting' ? $settings['vote_in_person_start'] ?? null : $settings['vote_by_proxy_start'] ?? null;
        $endDateTime = $votingType === 'Stockholder Online Voting' ? $settings['vote_in_person_end'] ?? null : $settings['vote_by_proxy_end'] ?? null;

        // Check if voting period is configured
        if (empty($startDateTime) || empty($endDateTime)) {
            Log::info("Voting Page: {$votingType} period is not configured.");
            return 'The administrator has yet to set the voting period for ' . $votingType . '. If you think this is an error, please contact your admin.';
        }

        $startDate = Carbon::parse($startDateTime);
        $endDate = Carbon::parse($endDateTime);

        Log::info("Voting Page: Checking {$votingType} period.");
        Log::info("Voting Page: Current time: {$currentDateTime}, Start: {$startDate}, End: {$endDate}");

        // Check if current time is within voting period
        if ($currentDateTime->between($startDate, $endDate)) {

            $votingType === 'Stockholder Online Voting' ? $this->stockholderOnlineActive = true : $this->proxyVotingActive = true;
            Log::info("Voting Page: {$votingType} is ongoing.");

            return 'Please click on this button to participate in the ' . $votingType . '.';
        }

        if ($votingType === 'Stockholder Online Voting') {

            if (!in_array(Auth::user()->role, ['stockholder', 'corp-rep'])) {

                $this->stockholderOnlineActive = false;
                Log::info("Voting Page: User can only access Stockholder Online Voting if they are a stockholder or corporate representative.");
                return 'Voting privileges in Stockholder Online Voting are exclusive to stockholders and corporate representatives.';
            }
        }


        // Generate appropriate message based on current time vs voting period
        return $this->generateVotingPeriodMessage($currentDateTime, $startDate, $endDate, $votingType);
    }


    public static function isElectionOngoing($votingType): bool|string
    {

        if (!in_array($votingType, ['Stockholder Online Voting', 'Proxy Voting'])) {
            Log::error("{$votingType}: Invalid voting type provided to electionOngoing method.", ['votingType' => $votingType]);
            throw new Exception("Invalid voting type provided: {$votingType}");
        }



        $currentDateTime = Carbon::now();
        $settings = ConfigService::getConfig();

        $startDateTime = $votingType === 'Stockholder Online Voting' ? $settings['vote_in_person_start'] ?? null : $settings['vote_by_proxy_start'] ?? null;
        $endDateTime = $votingType === 'Stockholder Online Voting' ? $settings['vote_in_person_end'] ?? null : $settings['vote_by_proxy_end'] ?? null;


        $startDate = Carbon::parse($startDateTime);
        $endDate = Carbon::parse($endDateTime);

        Log::info("{$votingType}: Checking voting period.");
        Log::info("{$votingType}: Current time: {$currentDateTime}, Start: {$startDate}, End: {$endDate}");

        // Check if voting period is configured
        if (empty($startDateTime) || empty($endDateTime)) {
            Log::info("{$votingType}: Voting period is not configured.");
            return 'The administrator has yet to set the voting period for ' . $votingType . '. If you think this is an error, please contact your admin.';
        }



        // Check if current time is within voting period
        if ($currentDateTime->between($startDate, $endDate)) {

            Log::info("{$votingType}: Voting is ongoing.");

            return true;
        }

        $dateFormat = 'F j, Y, \a\t g:i A';
        $formattedStartDate = $startDate->format($dateFormat);
        $formattedEndDate = $endDate->format($dateFormat);


        if ($currentDateTime->lt($startDate)) {
            Log::info("{$votingType}: Voting has not started yet.");
            return "The {$votingType} period will begin on {$formattedStartDate} and continue until {$formattedEndDate}.";
        }

        if ($currentDateTime->gt($endDate)) {
            Log::info("{$votingType}: Voting has ended.");
            return "The period for {$votingType} ended on {$formattedEndDate}.";
        }

        Log::warning("{$votingType}: Unexpected state in {$votingType} period check.");

        return false;
    }

    /**
     * Generate appropriate voting period message based on current time vs voting period
     * 
     * @param Carbon $currentDateTime Current date and time
     * @param Carbon $startDate Voting period start date
     * @param Carbon $endDate Voting period end date
     * @param string $votingType Type of voting (e.g., 'Stockholder Online Voting')
     * @return string Formatted message
     */
    private function generateVotingPeriodMessage(Carbon $currentDateTime, Carbon $startDate, Carbon $endDate, string $votingType): string
    {
        $dateFormat = 'F j, Y, \a\t g:i A';
        $formattedStartDate = $startDate->format($dateFormat);
        $formattedEndDate = $endDate->format($dateFormat);

        if ($currentDateTime->lt($startDate)) {
            Log::info("Voting Page: {$votingType} has not started yet.");
            return "The {$votingType} period will begin on {$formattedStartDate} and continue until {$formattedEndDate}.";
        }

        if ($currentDateTime->gt($endDate)) {
            Log::info("Voting Page: {$votingType} has ended.");
            return "The period for {$votingType} ended on {$formattedEndDate}.";
        }

        Log::warning("Voting Page: Unexpected state in {$votingType} period check.");

        // This shouldn't happen if called correctly, but just in case. This has been handled from the calling controller.
        return "Voting Page: The {$votingType} period is from {$formattedStartDate} to {$formattedEndDate}.";
    }





    /**
     * Validate voting items
     * @throws ValidationErrorException if no active items are found
     * Called by: StockholderOnlineBallotController@store, ProxyBallotController@store
     */
    public static function validateVotingItems()
    {

        Log::info('Validating voting items in validateVotingItems method.');

        $settings = ConfigService::getConfig();

        if ((int) $settings['amendment_enabled'] === 1) {
            $amendment = Amendment::where('isActive', true)->count();
            if ($amendment === 0) {
                Log::error('Amendment voting is enabled in settings but no active amendment found.');
                throw new ValidationErrorException('Amendment voting is enabled in settings but no active amendment found. Please contact admin.');
            }
        }


        $candidates = Candidate::where('isActive', true)->count();
        if ($candidates === 0) {
            Log::error('No active candidates found.');
            throw new ValidationErrorException('No active candidates found. Please contact admin.');
        }

        $agendaItems = Agenda::where('isActive', true)->count();
        if ($agendaItems === 0) {
            Log::error('No active agenda items found.');
            throw new ValidationErrorException('No active agenda items found. Please contact admin.');
        }
    }


    /**
     * Check if user can vote based on available votes and voting type
     * @param array $setting Configuration settings
     * @param array $availableVotes Available votes categorized by type
     * @param string $votingType Type of voting ('Stockholder Online Voting' or 'Proxy Voting')
     * @return bool True if user can vote, otherwise throws ValidationErrorException
     * @throws ValidationErrorException if user has no available votes
     * @throws Exception if an invalid voting type is provided
     * 
     */
    public static function checkIfUserCanVote($availableVotes, $votingType): bool
    {

        UtilityService::validateVotingType($votingType);

        Log::info("{$votingType}: Checking if user can vote");

        $amendmentEnabled = (int)ConfigService::getConfig('amendment_enabled') === 1;
        $bodVotes = count($availableVotes['bod']);
        $amendmentVotes = count($availableVotes['amendment']);

        Log::info("{$votingType}: Checking if amendment module is enabled", ['enabled' => $amendmentEnabled]);

        if ($amendmentEnabled) {
            Log::info("{$votingType}: Amendment module is enabled, checking both BOD and Amendment votes.", [
                'bodVotes' => $bodVotes,
                'amendmentVotes' => $amendmentVotes
            ]);

            if ($bodVotes === 0 && $amendmentVotes === 0) {
                $msg = "You don't have any votes available for {$votingType}.";
                Log::info("{$votingType}: No available votes found for both BOD and Amendment.", [
                    'votingType' => $votingType,
                    'bodVotes' => $bodVotes,
                    'amendmentVotes' => $amendmentVotes
                ]);
                ActivityController::log(['activityCode' => '00090', 'remarks' => $msg, 'userId' => Auth::user()->id]);
                throw new ValidationErrorException($msg);
            }
            return true;
        }

        Log::info("{$votingType}: Amendment module is disabled, checking BOD votes only.", ['bodVotes' => $bodVotes]);



        if ($bodVotes === 0) {
            $msg = "You don't have any votes available for {$votingType}.";
            Log::info("{$votingType}: No available votes found for BOD.", [
                'votingType' => $votingType,
                'bodVotes' => $bodVotes
            ]);

            $activityCode = $votingType === 'Stockholder Online Voting' ? '00090' : '00091';
            ActivityController::log(['activityCode' => $activityCode, 'remarks' => $msg, 'userId' => Auth::user()->id]);
            throw new ValidationErrorException($msg);
        }

        return true;
    }


    public static function generateBallot($votingType): array
    {

        if (!in_array($votingType, ['Stockholder Online Voting', 'Proxy Voting'])) {

            Log::error("{$votingType}: Invalid voting type provided to generateBallot method.", ['votingType' => $votingType]);
            throw new Exception('Invalid voting type: ' . $votingType);
        }

        $form = $votingType === 'Stockholder Online Voting' ? 'person' : 'proxy';


        $ballotId = UtilityService::generateId('ballots', 'ballotId');

        Log::info("{$votingType}: Generated ballot ID: {$ballotId}", ['votingType' => $votingType]);


        $lastBallotNo = Ballot::selectRaw('MAX(CAST(trim(LEADING "0" FROM ballotNo) AS UNSIGNED)) AS lastBallotNo')
            ->where('ballotType', $form)
            ->first();

        $ballotNo = $lastBallotNo === null ? '0001' : $lastBallotNo->lastBallotNo + 1;

        $ballotNo = str_pad($ballotNo, 4, "0", STR_PAD_LEFT);

        Log::info("{$votingType}: Generated ballot No: {$ballotNo}", ['votingType' => $votingType]);

        return [
            'ballotNo' => $ballotNo,
            'ballotId' => $ballotId
        ];
    }

    public function processUserSubmittedData(Request $request, $ballotInfo): array
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        Log::info("{$votingType}: Processing user-submitted voting data in processUserSubmittedData for ballot ID: " . $request->ballotId, [
            'ballotId' => $request->ballotId,
            'ballotType' => $ballotInfo->ballotType,
            'ip' => $request->ip(),

        ]);

        $amendmentSummary = $this->processAmendmentSummary($request->amendment, $ballotInfo);
        $agendaSummary = $this->processAgendaSummary($request->agenda, $ballotInfo);
        $bodSummary = $this->processBodSummary($request->bod, $ballotInfo);

        $proccessedData = [
            'bod' => $bodSummary,
            'amendment' => $amendmentSummary,
            'agenda' => $agendaSummary,
        ];

        Log::info('Completed processing user-submitted voting data in processUserSubmittedData for ballot ID: ' . $request->ballotId, [
            'ballotId' => $request->ballotId,
            'ip' => $request->ip(),
            'processedData' => $proccessedData
        ]);
        return $proccessedData;
    }

    private function processBodSummary(array $bod, Ballot $ballotInfo)
    {


        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $candidateIds = array_column($bod, 'candidateId');

        $bodCollection = Candidate::where('isActive', 1)->get();

        $bodArr = [];

        foreach ($bodCollection as $candidate) {

            $key = array_search($candidate->candidateId, $candidateIds);

            if ($key === false) {

                Log::error("{$votingType}: Candidate ID {$candidate->candidateId} not found in the submitted data.");

                throw new ValidationErrorException("{$votingType}: Candidate ID {$candidate->candidateId} not found in the submitted data.");
            }

            $this->validateBodForm($bod[$key], $candidate);

            $bodArr[] = array(
                'candidateId' => $candidate->candidateId,
                'name' => $candidate->lastName . ', ' . $candidate->firstName . ' ' . $candidate->middleName,
                'type' => $candidate->type,
                'vote' => (int)$bod[$key]['vote']
            );
        }

        if (count($bodArr) !== count($candidateIds)) {
            Log::error("{$votingType}: Mismatch in BOD items count. Expected " . count($candidateIds) . " but got " . count($bodArr) . ".");
            throw new ValidationErrorException("{$votingType}: There was an error processing your board of director items. Please try again.");
        }
        return $bodArr;
    }


    private function processAmendmentSummary(array $amendments, Ballot $ballotInfo)
    {

        $amendmentIds = array_column($amendments, 'amendmentId');
        $amendmentsCollection = Amendment::where('isActive', 1)->get();

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);


        $amendmentArr = [];

        foreach ($amendmentsCollection as $amendment) {

            $key = array_search($amendment->amendmentId, $amendmentIds);

            if ($key === false) {

                Log::error("{$votingType}: Amendment ID {$amendment->amendmentId} not found in the submitted data.");

                throw new ValidationErrorException("{$votingType}: Amendment ID {$amendment->amendmentId} not found in the submitted data.");
            }

            $this->validateAmendmentForm($amendments[$key], $ballotInfo);

            $amendmentArr[] = array(
                'amendmentId' => $amendment->amendmentId,
                'amendment' => $amendment->amendmentDesc,
                'vote' => $amendments[$key]
            );
        }

        if (count($amendmentArr) !== count($amendmentIds)) {
            Log::error("{$votingType}: Mismatch in amendment items count. Expected " . count($amendmentIds) . " but got " . count($amendmentArr) . ".");
            throw new ValidationErrorException("{$votingType}: There was an error processing your amendment items. Please try again.");
        }

        return $amendmentArr;
    }




    private function processAgendaSummary(array $agendas, Ballot $ballotInfo)
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $agendaIds = array_column($agendas, 'agendaId');

        $amendmentsCollection = Agenda::where('isActive', 1)->get();

        $agendaArr = [];

        foreach ($amendmentsCollection as $amendment) {

            $key = array_search($amendment->agendaId, $agendaIds);

            if ($key === false) {

                Log::error("{$votingType}: Agenda ID {$amendment->agendaId} not found in the submitted data.");

                throw new ValidationErrorException("{$votingType}: Agenda ID {$amendment->agendaId} not found in the submitted data.");
            }

            $this->validateAgendaForm($agendas[$key], $ballotInfo);

            $agendaArr[] = array(
                'agendaId' => $amendment->agendaId,
                'agenda' => $amendment->agendaDesc,
                'vote' => $agendas[$key]
            );
        }


        if (count($agendaArr) !== count($agendaIds)) {
            Log::error("{$votingType}: Mismatch in agenda items count. Expected " . count($agendaIds) . " but got " . count($agendaArr) . ".");
            throw new ValidationErrorException("{$votingType}: There was an error processing your agenda items. Please try again.");
        }

        return $agendaArr;
    }

    public function validateAgendaForm($agenda, $ballotInfo)
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        if ($ballotInfo->availableVotesBod === 0) {
            Log::info("{$votingType}: Skip agenda form validation, no available BOD votes for ballot ID " . $ballotInfo->ballotId);
            return;
        }

        $selectedVotes = (int) $agenda['favor'] + (int) $agenda['notFavor'] + (int) $agenda['abstain'];

        if ($selectedVotes !== 1) {
            Log::error("{$votingType}: Form validation error: Invalid selection for agenda item ID " . $agenda['agendaId']);
            throw new ValidationErrorException("{$votingType}: Form validation error: Please select exactly one option (\"favor\", \"not favor\", or \"abstain\") for each agenda item.");
        }
    }

    public function validateAmendmentForm(array $amendment, Ballot $ballotInfo)
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);
        if ($ballotInfo->availableVotesAmendment === 0) {
            Log::info("{$votingType}: Skip amendment {$amendment['amendmentId']} form validation, no available amendment votes for ballot ID " . $ballotInfo->ballotId);
            return;
        }
        $selectedVotes = (int) $amendment['yes'] + (int) $amendment['no'];

        if ($selectedVotes !== 1) {
            Log::error("{$votingType}: Form validation error: Invalid selection for amendment ID {$amendment['amendmentId']}");
            throw new ValidationErrorException('Form validation error: Please select exactly one option ("yes" or "no") for each amendment.');
        }
    }


    public static function ballotInfo($request, $votingType): Ballot
    {


        Log::info("{$votingType}: Fetching ballot information for ballot ID " . $request->ballotId, [
            'ballotId' => $request->ballotId,
            'votingType' => $votingType,
        ]);

        UtilityService::validateVotingType($votingType);

        $ballotType = $votingType === 'Stockholder Online Voting' ? 'person' : 'proxy';


        $ballotInfo = Ballot::where('ballotId', $request->ballotId)
            ->where('ballotType', $ballotType)
            ->where('isViewed', 1)
            ->where('createdBy', Auth::user()->id)
            ->first();

        if (!$ballotInfo) {
            Log::error("{$votingType}: Ballot not found or invalid ballot type for ballot ID " . $request->ballotId, [
                'ballotId' => $request->ballotId,
            ]);
            throw new  ValidationErrorException('Ballot not found. Please contact support if you believe this is an error.');
        }

        if ($ballotInfo->isSubmitted === 1) {
            Log::error("{$votingType}: Ballot has already been submitted for ballot ID " . $request->ballotId, [
                'ballotId' => $request->ballotId,
            ]);
            throw new  ValidationErrorException('This ballot has already been submitted. Please contact support if you believe this is an error.');
        }

        return $ballotInfo;
    }

    public function checkAccountChanges(Ballot $ballot): bool
    {

        $votingType = UtilityService::getVotingType($ballot->ballotType);

        Log::info("{$votingType}: Checking for account changes for ballot ID {$ballot->ballotId}", [
            'ballotId' => $ballot->ballotId,
            'ballotType' => $ballot->ballotType
        ]);


        $newAvailableAccounts = $this->getAvailableAccounts($ballot->ballotType);
        $oldAvailableAccounts = json_decode($ballot->availableAccounts, true);

        if (json_encode($newAvailableAccounts) != $ballot->availableAccounts) {

            Log::info("{$votingType}: Detected changes in available accounts for ballot ID {$ballot->ballotId}", [
                'ballotId' => $ballot->ballotId,
                'ballotType' => $ballot->ballotType,
                'previousAvailableAccounts' => $oldAvailableAccounts,
                'newAvailableAccounts' => json_encode($newAvailableAccounts, true)
            ]);

            return true;
        }

        Log::info("{$votingType}: No changes in available accounts detected for ballot ID {$ballot->ballotId}", [
            'ballotId' => $ballot->ballotId,
            'availableAccounts' => $ballot->availableAccounts
        ]);
        return false;
    }

    public function createAvailableVoteChangeActivityLog($ballot, $ballotConfirmation = null, $message = null)
    {

        $activityCode = $ballot->ballotType === 'person' ? '00090' : '00091';



        ActivityController::log([
            'activityCode' => $activityCode,
            'remarks' => $message ?? 'There has been a change in your available accounts since you generated your ballot. Please reload the page to get the updated list of available shares.',
            'userId' => Auth::user()->id,
            'ballotId' => $ballot->ballotId,
            'confirmationId' =>  $ballotConfirmation?->confirmationId
        ]);
    }



    private function getAvailableAccounts($ballotType): array
    {

        if (!in_array($ballotType, ['person', 'proxy'])) {
            Log::error('Invalid ballot type provided to getAvailableAccounts method.', ['ballotType' => $ballotType]);
            throw new Exception('Invalid ballot type: ' . $ballotType);
        }


        if ($ballotType === 'person') {
            $stockholderOnlineBallotController = new StockholderOnlineBallotService();
            return $stockholderOnlineBallotController->getAvailableAccounts();
        }
        $proxyVotingBallotController = new ProxyVotingBallotService();
        return $proxyVotingBallotController->getAvailableVotes(Auth::user()) ?? [];


        return [];
    }

    public function checkUnusedVotes($ballotInfo, $totalVotesSubmitted, $unusedVotes): string|bool
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        Log::info("{$votingType}: Checking for unused votes for ballot ID " . $ballotInfo->ballotId, [
            'ballotId' => $ballotInfo->ballotId,
            'availableVotesBod' => $ballotInfo->availableVotesBod,
            'totalVotesSubmitted' => $totalVotesSubmitted,
            'unusedVotes' => $unusedVotes
        ]);

        if ($unusedVotes > 0) {


            $message = "Once confirmed, you will no longer be allowed to make any changes to this ballot. All of your $unusedVotes unused vote(s) will be voided in this submission and will not be counted. You will also receive an email confirming that your votes have been submitted to the Club.";

            ActivityController::log([
                'activityCode' => $ballotInfo->ballotType === 'person' ? '00090' : '00091',
                'remarks' => $message,
                'ballotId' => $ballotInfo->ballotId,
                'userId' => Auth::user()->id
            ]);

            Log::info("{$votingType}: User has $unusedVotes unused votes.", [
                'ballotId' => $ballotInfo->ballotId,
                'availableVotesBod' => $ballotInfo->availableVotesBod,
                'totalVotesSubmitted' => $totalVotesSubmitted,
                'unusedVotes' => $unusedVotes
            ]);



            return $message;
        }

        return true;
    }


    public function checkExceedVotes($userSubmittedData, $ballotInfo, int $totalVotesSubmitted): void
    {

        $votingType = $ballotInfo->ballotType === 'person' ? 'Stockholder Online Voting' : 'Proxy Voting';



        if ($totalVotesSubmitted > $ballotInfo->availableVotesBod) {
            Log::info("{$votingType}: User exceeded available votes", [
                'ballotId' => $ballotInfo->ballotId,
                'availableVotesBod' => $ballotInfo->availableVotesBod,
                'totalVotesSubmitted' => $totalVotesSubmitted,
            ]);

            $message = "You have exceeded your available votes. Total votes should not exceed " . $ballotInfo->availableVotesBod . " vote(s). Please review your distribution.";

            $this->recordExceedVotes($ballotInfo, $userSubmittedData, $message);

            Log::error("{$votingType}: User exceeded available votes for ballot ID " . $ballotInfo->ballotId, [
                'ballotId' => $ballotInfo->ballotId,
                'availableVotesBod' => $ballotInfo->availableVotesBod,
                'totalVotesSubmitted' => $totalVotesSubmitted,
            ]);
            throw new ValidationErrorException($message);
        }
    }


    private function recordExceedVotes($ballotInfo, $userSubmittedData, $message): BallotConfirmation
    {


        $ballotConfirmationService = new ConfirmationService();
        $ballotConfirmation = $ballotConfirmationService->store($ballotInfo, $userSubmittedData, false, $message);

        $activityCode = $ballotInfo->ballotType === 'person' ? '00090' : '00091';
        ActivityController::log([
            'activityCode' => $activityCode,
            'remarks' => $message,
            'userId' => Auth::user()->id,
            'ballotId' => $ballotInfo->ballotId,
            'confirmationId' =>  $ballotConfirmation->confirmationId

        ]);

        return $ballotConfirmation;
    }

    private function recordUnusedVotes($ballotInfo, $userSubmittedData, $message): BallotConfirmation
    {


        $ballotConfirmationService = new ConfirmationService();
        $ballotConfirmation = $ballotConfirmationService->store($ballotInfo, $userSubmittedData, true, $message);

        $activityCode = $ballotInfo->ballotType === 'person' ? '00090' : '00091';
        ActivityController::log([
            'activityCode' => $activityCode,
            'remarks' => $message,
            'userId' => Auth::user()->id,
            'ballotId' => $ballotInfo->ballotId,
            'confirmationId' =>  $ballotConfirmation->confirmationId

        ]);

        return $ballotConfirmation;
    }

    private function validateBodForm(array $bod, Candidate $candidate)
    {


        $votingType = UtilityService::getVotingType($candidate->ballotType ?? 'person');

        $candidateName = $candidate->lastName . ' ' . $candidate->firstName;

        if (!isset($bod['vote'])) {
            Log::error("{$votingType}: Form validation error: Incorrect vote format for candidate " . $candidateName, [
                'candidateId' => $candidate->candidateId,
                'name' => $candidateName,
                'vote' => $bod['vote'] ?? null
            ]);
            throw new ValidationErrorException("{$votingType}: Form validation error: The vote for candidate " . $candidateName . " is not formatted correctly.");
        }

        if (!is_numeric($bod['vote']) || (int)$bod['vote'] < 0) {
            Log::error("{$votingType}: Form validation error: Invalid vote value for candidate " . $candidateName, [
                'candidateId' => $candidate->candidateId,
                'name' => $candidateName,
                'vote' => $bod['vote'] ?? null
            ]);
            throw new ValidationErrorException("{$votingType}: Form validation error: Vote for candidate " . $candidateName . " must be a non-negative number.");
        }

        if ((string)(int)$bod['vote'] !== (string)$bod['vote']) {
            Log::error("{$votingType}: Form validation error: Vote is not an integer for candidate " . $candidateName, [
                'candidateId' => $candidate->candidateId,
                'name' => $candidateName,
                'vote' => $bod['vote'] ?? null
            ]);
            throw new ValidationErrorException("{$votingType}: Form validation error: Vote for candidate " . $candidateName . " must be a valid whole number.");
        }
    }



    public static function ensureEmailIsNotUsed($email, $votingType): void
    {

        UtilityService::validateVotingType($votingType);

        $ballotType = $votingType === 'Stockholder Online Voting' ? 'person' : 'proxy';

        Log::info("{$votingType}: Checking if email {$email} has already submitted a ballot.", [
            'email' => $email,
            'votingType' => $votingType
        ]);

        $ballot = Ballot::where('email', $email)->where('ballotType', $ballotType)->where('isSubmitted', true);

        if ($ballot->exists()) {

            Log::info("{$votingType}: Attempted to create a ballot form for {$votingType}, but a submitted ballot already exists for {$email}.");
            ActivityController::log([
                'activityCode' => $votingType === 'Stockholder Online Voting' ? '00090' : '00091',
                'remarks' => "Attempted to create a ballot form for {$votingType} but already has a submitted ballot using {$email}.",
                'userId' => Auth::user()->id
            ]);
            throw new ValidationErrorException("Sorry, you cannot proceed because you have already submitted your vote for {$votingType}. If you believe this is an error, please contact your administrator.");
        }
    }

    /**
     * Save attendance records for available votes
     * @param array $availableVotes Array containing available votes categorized by type (e.g., 'bod', 'amendment')
     * @param Ballot $ballot The ballot instance for which attendance is being recorded
     * @param string $votingType Type of voting ('Stockholder Online Voting' or 'Proxy Voting')
     * @throws Exception if an invalid voting type is provided or if attendance creation fails
     */

    public static function saveAttendance($availableVotes, $ballot, $votingType)
    {

        UtilityService::validateVotingType($votingType);



        Log::info("{$votingType}: Creating attendance record", [
            'ballotType' => $ballot->ballotType,
            'ballotId' => $ballot->ballotId,
            'availableVotes' => $availableVotes
        ]);

        $attendance = [];

        foreach ($availableVotes['bod'] as $availableVote) {
            $attendance[] = array(
                'accountId' => $availableVote,
                'ballotId' => $ballot->ballotId,
                'voteType' => 'bod',
                'createdBy' => Auth::id(),
                'createdAt' => now()
            );
        }
        foreach ($availableVotes['amendment'] as $availableVote) {
            $attendance[] = array(
                'accountId' => $availableVote,
                'ballotId' => $ballot->ballotId,
                'voteType' => 'amendment',
                'createdBy' => Auth::id(),
                'createdAt' => now()
            );
        }

        Log::info("{$votingType}: Attendance has been created", ['ballotType' => $ballot->ballotType, 'attendance' => $attendance]);
    }

    public function insertUsedAccounts($confirmation)
    {


        $ballotInfo = $confirmation->ballot;
        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $data = json_decode($confirmation->availableVotes, true);

        $bodData = $data['bod'];
        $amendmentData = $data['amendment'];


        $bod = $this->createUseBodAccount($bodData, $confirmation);
        $amendment = $this->createUsedAmendmentAccount($amendmentData, $confirmation);

        if (!$bod && !$amendment) {
            Log::warning("{$votingType}: No used accounts to insert for confirmation ID " . $confirmation->confirmationId, [
                'confirmationId' => $confirmation->confirmationId,
                'bodData' => $bodData,
                'amendmentData' => $amendmentData
            ]);

            throw new Exception('No used accounts to insert.');
        }

        return true;
    }

    private function createUseBodAccount($boardOfDirectors, $confirmation): bool
    {



        if (empty($boardOfDirectors)) {
            return false;
        }

        $bodAccounts = [];
        foreach ($boardOfDirectors as $accountId) {

            $bodAccounts[] = [
                'ballotId' => $confirmation->ballotId,
                'accountId' => $accountId,
                'createdBy' => Auth::id()
            ];
        }

        $usedBodAccounts = UsedBoardOfDirectorAccount::insert($bodAccounts);

        return $usedBodAccounts;
    }

    private function createUsedAmendmentAccount($amendments, $confirmation): bool
    {

        if (empty($amendments)) {
            return false;
        }

        $amendmentAccounts = [];
        foreach ($amendments as $accountId) {
            $amendmentAccounts[] = [
                'ballotId' => $confirmation->ballotId,
                'accountId' => $accountId,
                'createdBy' => Auth::id()
            ];
        }

        $usedAmendmentAccounts = UsedAmendmentAccount::insert($amendmentAccounts);

        return $usedAmendmentAccounts;
    }


    public static function generateSummaryInfoMessage($usedAllVotes, $unusedVotes, bool $confirmMessage = true): string
    {
        if ($usedAllVotes !== true) {
            return $confirmMessage ?
                "Once confirmed, you will no longer be allowed to make any changes to this ballot. All of your $unusedVotes unused vote(s) will be voided in this submission and will not be counted. You will also receive an email confirming that your votes have been submitted to the Club." :
                "Once confirmed, you will no longer be allowed to make any changes to this ballot. All of your $unusedVotes unused vote(s) will be voided in this submission and will not be counted.";
        }


        return $confirmMessage ?
            "Once confirmed, you will no longer be allowed to make any changes to this ballot. You will also receive an email confirming that your votes have been submitted to the Club." :
            "Once confirmed, you will no longer be allowed to make any changes to this ballot.";
    }

    public function updateBallotStatus($ballotInfo, $ballotConfirmation)
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        Log::info("{$votingType}: Updating ballot status to submitted for ballot ID " . $ballotInfo->ballotId, [
            'ballotId' => $ballotInfo->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId
        ]);

        Log::info("{$votingType}: Decoding ballot confirmation data", [
            'confirmationData' => $ballotConfirmation->data
        ]);


        $confirmationData = json_decode($ballotConfirmation->data);

        Log::info("{$votingType}: Decoded ballot confirmation data for BOD", [
            'confirmationData' => $confirmationData
        ]);

        $totalVotesSubmitted = collect($confirmationData->bod)->sum(function ($bod) {
            return isset($bod->vote) && is_numeric($bod->vote) ? (int)$bod->vote : 0;
        });


        $unusedVotesBod = $ballotInfo->availableVotesBod - $totalVotesSubmitted;
        $ballotInfo->update([
            'castedVotes' => $totalVotesSubmitted,
            'unusedVotesBod' => $unusedVotesBod,
            'isSubmitted' => true,
            'submittedAt' => Carbon::now(),
            'confirmationId' => $ballotConfirmation->confirmationId,
        ]);

        Log::info("{$votingType}: Ballot status updated to submitted for ballot ID " . $ballotInfo->ballotId, [
            'ballotId' => $ballotInfo->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId,
            'castedVotes' => $totalVotesSubmitted,
            'unusedVotesBod' => $unusedVotesBod
        ]);
    }


    public function createBallotDetails($confirmation, $ballotInfo)
    {


        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $candidateData = $this->processBodForCreation($confirmation, $ballotInfo);

        Log::info("{$votingType}: Inserting ballot details", [
            'candidateData' => $candidateData,
            'votingType' => $votingType
        ]);

        if (!empty($candidateData)) {
            $ballotDetail = BallotDetail::insert($candidateData);

            Log::info("{$votingType}: Ballot details inserted successfully.", ['votingType' => $votingType, 'ballotDetails' => $candidateData]);

            return $ballotDetail;
        }

        Log::info("{$votingType}: No ballot details to insert.", ['votingType' => $votingType]);

        return false;
    }


    public function createBallotAgendaDetails($confirmation, $ballotInfo)
    {
        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $agendaData = $this->processAgendaForCreation($confirmation, $ballotInfo);

        Log::info("{$votingType}: Inserting ballot agenda details", [
            'agendaData' => $agendaData,
            'votingType' => $votingType
        ]);

        if (!empty($agendaData)) {

            $ballotAgenda = BallotAgenda::insert($agendaData);

            Log::info("{$votingType}: Ballot agenda details inserted successfully.", ['votingType' => $votingType, 'ballotAgendaDetails' => $agendaData]);
            return $ballotAgenda;
        }

        Log::info("{$votingType}: No ballot agenda details to insert.", ['votingType' => $votingType]);

        return false;
    }


    public function createBallotAmendmentDetails($confirmation, $ballotInfo)
    {


        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $amendmentData = $this->processAmendmentForCreation($confirmation, $ballotInfo);

        Log::info("{$votingType}: Inserting ballot amendment details", [
            'amendmentData' => $amendmentData,
            'votingType' => $votingType
        ]);

        if (!empty($amendmentData)) {
            $ballotAmendment = BallotAmendment::insert($amendmentData);
            Log::info("{$votingType}: Ballot amendment details inserted successfully.", ['votingType' => $votingType, 'ballotAmendmentDetails' => $amendmentData]);
            return $ballotAmendment;
        }

        Log::info("{$votingType}: No ballot amendment details to insert.", ['votingType' => $votingType]);

        return false;
    }


    private function processAgendaForCreation($ballotConfirmation, $ballotInfo)
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        Log::info("{$votingType}: Processing agenda votes for creation for ballot ID " . $ballotConfirmation->ballotId, [
            'ballotId' => $ballotConfirmation->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId,
        ]);

        if ($ballotInfo->availableVotesBod === 0) {

            Log::info("{$votingType}: No available votes for agenda for ballot ID " . $ballotConfirmation->ballotId, [
                'ballotId' => $ballotConfirmation->ballotId,
                'confirmationId' => $ballotConfirmation->confirmationId,
            ]);
            return [];
        }

        $voteService = new VoteService();
        $data = json_decode($ballotConfirmation->data, true);
        $agendaData = $data['agenda'] ?? [];

        $agendaProcessedData = [];

        foreach ($agendaData as $agenda) {

            $voteService->validateAgendaForm($agenda["vote"], $ballotInfo);

            $agendaProcessedData[] = [
                'favor' => $agenda['vote']['favor'],
                'notFavor' => $agenda['vote']['notFavor'],
                'abstain' => $agenda['vote']['abstain'],
                'agendaId' => $agenda['agendaId'],
                'ballotId' => $ballotInfo->ballotId,
                'createdBy' => Auth::id()

            ];
        }

        Log::info("{$votingType}: Processed " . count($agendaProcessedData) . " agenda votes for creation for ballot ID " . $ballotConfirmation->ballotId, [
            'ballotId' => $ballotConfirmation->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId,
            'processedVotes' => count($agendaProcessedData),
            'data' => $agendaProcessedData
        ]);

        return $agendaProcessedData;
    }

    private function processAmendmentForCreation($ballotConfirmation, $ballotInfo)
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        Log::info("{$votingType}: Processing amendment votes for creation for ballot ID " . $ballotConfirmation->ballotId, [
            'ballotId' => $ballotConfirmation->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId,
        ]);

        if ($ballotInfo->availableVotesAmendment === 0) {

            Log::info("{$votingType}: No available votes for amendment for ballot ID " . $ballotConfirmation->ballotId, [
                'ballotId' => $ballotConfirmation->ballotId,
                'confirmationId' => $ballotConfirmation->confirmationId,
            ]);
            return [];
        }

        $voteService = new VoteService();
        $data = json_decode($ballotConfirmation->data, true);
        $amendmentData = $data['amendment'] ?? [];

        $amendmentProcessedData = [];

        foreach ($amendmentData as $amendment) {

            $voteService->validateAmendmentForm($amendment["vote"], $ballotInfo);

            $amendmentProcessedData[] = [
                'yes' => $amendment['vote']['yes'],
                'no' => $amendment['vote']['no'],
                'amendmentId' => $amendment['amendmentId'],
                'ballotId' => $ballotInfo->ballotId,
                'createdBy' => Auth::id()

            ];
        }

        Log::info("{$votingType}: Processed " . count($amendmentProcessedData) . " amendment votes for creation for ballot ID " . $ballotConfirmation->ballotId, [
            'ballotId' => $ballotConfirmation->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId,
            'processedVotes' => count($amendmentProcessedData),
            'data' => $amendmentProcessedData
        ]);

        return $amendmentProcessedData;
    }

    private function processBodForCreation($ballotConfirmation, $ballotInfo)
    {


        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        Log::info("{$votingType}: Processing Board of Directors votes for creation for ballot ID " . $ballotConfirmation->ballotId, [
            'ballotId' => $ballotConfirmation->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId,
        ]);

        if ($ballotInfo->availableVotesBod === 0) {

            Log::info("{$votingType}: No available votes for Board of Directors for ballot ID " . $ballotConfirmation->ballotId, [
                'ballotId' => $ballotConfirmation->ballotId,
                'confirmationId' => $ballotConfirmation->confirmationId,
            ]);
            return [];
        }



        $data = json_decode($ballotConfirmation->data, true);
        $bodData = $data['bod'] ?? [];

        $candidateData = [];

        foreach ($bodData as $candidate) {
            if ((int)$candidate['vote'] > 0) {
                $candidateData[] = [
                    'vote' => $candidate['vote'],
                    'candidateId' => $candidate['candidateId'],
                    'ip' => request()->ip(),
                    'ballotId' => $ballotConfirmation->ballotId,
                    'createdBy' => Auth::id(),
                ];
            }
        }


        Log::info("{$votingType}: Processed " . count($candidateData) . " Board of Directors votes for creation for ballot ID " . $ballotConfirmation->ballotId, [
            'ballotId' => $ballotConfirmation->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId,
            'processedVotes' => count($candidateData),
            'data' => $candidateData
        ]);
        return $candidateData;
    }

    public static function sendVoteConfirmation($email, $votingType, $confirmationId, $ballotInfo)
    {

        $sendVotingConfirmationReceiptEnabled = ConfigService::getConfig('send_voting_confirmation_receipt_enabled') === '1';


        if (!$sendVotingConfirmationReceiptEnabled) {
            Log::info("{$votingType}: Sending vote confirmation email is disabled in configuration. Skipping email sending to " . $email, [
                'confirmationId' => $confirmationId
            ]);
            return;
        }


        $votingType = UtilityService::validateVotingType($votingType);

        Log::info("{$votingType}: Sending vote confirmation email to " . $email, [
            'confirmationId' => $confirmationId
        ]);


        try {

            $subject = "Vote Successfully Recorded - Valley Golf and Country Club, Inc.";

            Mail::to($email)->send(new VoteSuccessMail($subject));

            Log::info("{$votingType}: Vote confirmation email sent successfully to " . $email, [
                'confirmationId' => $confirmationId
            ]);
        } catch (Exception $e) {

            $transactionId = request()->attributes->get('transaction_id');

            ActivityController::log([
                'activityCode' => $votingType === 'Stockholder Online Voting' ? '00094' : '00095',
                'remarks' => "Warning: Failed to send vote confirmation email to {$email}. Transaction ID: {$transactionId}",
                'userId' => Auth::user()->id,
                'ballotId' => $ballotInfo->ballotId,
                'confirmationId' => $confirmationId
            ]);

            Log::error("{$votingType}: Failed to send vote confirmation email to " . $email, [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,
                'email' => $email,
                'confirmationId' => $confirmationId
            ]);
        }
    }


    public static function generateAmendmentForm($amendments, $availableSharesAmendment): string
    {
        $counter = 1;

        $amendmentForm = '';

        foreach ($amendments as $amendment) {

            $disabledAttr = $availableSharesAmendment === 0 ? 'disabled' : '';
            $disabledStyle = $availableSharesAmendment === 0 ? 'pure-button-disabled' : '';

            $amendmentForm .= '<tr data-id="' . $amendment->amendmentId . '">
                                <td class="counter td-amend">' . $counter . '</td>
                                <td class="amendment td-amend">' . $amendment->amendmentDesc . '</td>
                                <td class="text-right">
                                    <button type="button" class="btn border-success-custom btn-amendment btn-yes ' . $disabledStyle . '" ' . $disabledAttr . '>Favor</button>
                                    <button type="button" class="btn border-success-custom btn-amendment btn-no ' . $disabledStyle . '" ' . $disabledAttr . '>Not in Favor</button>
                                </td>
                            </tr>';

            $counter++;
        }


        return $amendmentForm;
    }


    public static function generateAgendaForm($agendas, $availableSharesBod): string
    {
        $counter = 1;


        $agendaForm = '';

        foreach ($agendas as $agenda) {

            $disabledAttr = '';
            $disabledStyle = '';
            $tooltipAgenda = '';

            if ($availableSharesBod === 0) {

                $disabledStyle = 'pure-button-disabled';
                $disabledAttr = 'disabled';

                $tooltipAgenda = 'data-toggle="tooltip" title="No available share for Agenda."';
            }


            $agendaForm .= '<tr data-id="' . $agenda->agendaId . '">
                              <td class="counter td-amend">' . $counter . '</td>
                              <td class="amendment td-amend">' . $agenda->agendaDesc . '</td>
                              <td class="td-agenda-right">
                                <button type="button" class="btn border-success-custom btn-amendment btn-agenda btn-favor ' . $disabledStyle . '" ' . $tooltipAgenda . ' ' . $disabledAttr . '>Yes</button>
                                <button type="button" class="btn border-success-custom btn-amendment btn-agenda btn-not-favor ' . $disabledStyle . '" ' . $tooltipAgenda . ' ' . $disabledAttr . '>No</button>
                                <button type="button" class="btn border-success-custom btn-amendment btn-agenda btn-abstain ' . $disabledStyle . '" ' . $tooltipAgenda . ' ' . $disabledAttr . '>Abstain</button>
                              </td>
                          </tr>';

            $counter++;
        }

        return $agendaForm;
    }
}
