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
use App\Models\Ballot;
use App\Models\BallotAgenda;
use App\Models\BallotAmendment;
use App\Models\BallotConfirmation;
use App\Models\BallotDetail;
use App\Models\Candidate;
use App\Models\UsedAmendmentAccount;
use App\Models\UsedBoardOfDirectorAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


class BallotService
{

    protected $stockholderOnlineActive = false;
    protected $proxyVotingActive = false;
    protected array $configSettings = [];
    protected string $votingType;
    protected string $ballotType;
    protected bool $isBodEnabled;
    protected bool $isAmendmentEnabled;


    public function __construct()
    {
        $this->configSettings = ConfigService::getConfig();
        $this->isBodEnabled = $this->configSettings['bod_module_enabled'] ?? false;
        $this->isAmendmentEnabled = $this->configSettings['amendment_enabled'] ?? false;
    }

    /**
     * Set the voting type for the ballot service. This method also sets the corresponding ballot type based on the provided voting type.
     * 
     * @param string $votingType The voting type to set. Valid values are 'Stockholder Online Voting' and 'Proxy Voting'.
     * @throws Exception If an invalid voting type is provided.
     */
    public function setVotingType(string $votingType): void
    {
        if (!in_array($votingType, ['Stockholder Online Voting', 'Proxy Voting'])) {
            throw new Exception("Invalid voting type provided: {$votingType}");
        }

        $this->votingType = $votingType;
        $this->ballotType  = $votingType === 'Stockholder Online Voting' ? 'person' : 'proxy';
    }

    public function getVotingType(): string
    {
        if (empty($this->votingType)) {
            throw new Exception("Voting type has not been set. Please call setVotingType() before accessing the voting type.");
        }
        return $this->votingType;
    }

    public function getBallotType(): string
    {
        if (empty($this->ballotType)) {
            throw new Exception("Ballot type has not been set. Please call setVotingType() before accessing the ballot type.");
        }
        return $this->ballotType;
    }

    public function getConfigSettings(): array
    {
        if (empty($this->configSettings)) {
            $this->configSettings = ConfigService::getConfig();
        }

        return $this->configSettings;
    }



    public function create(Request $request)
    {


        $user = Auth::user();
        $fullName = Auth::user()->authorized_signatory;




        $amendmentEnabled = ConfigService::isAmendmentEnabled();
        $bodEnabled = ConfigService::isBodEnabled();


        $this->checkConfig($bodEnabled, $amendmentEnabled);

        $userIds = (new OnlineAccountService())->getAccounts(email: $user->email, canUseVoteOnly: true);
        Log::info("User IDs associated with email {$user->email}: " . implode(', ', $userIds));


        $proxyBod = $this->getProxyBod($userIds, $bodEnabled);
        $proxyAmendment = $this->getProxyAmendment($userIds, $amendmentEnabled);

        Log::info("Voting Page: Retrieved proxy BOD and amendment data", [
            'proxyBodCount' => count($proxyBod),
            'proxyAmendmentCount' => count($proxyAmendment)
        ]);

        // Log user details for debugging
        foreach (User::with('stockholder', 'stockholderAccount', 'nonMemberAccount', 'adminAccount')->whereIn('id', $userIds)->get() as $user) {
            Log::info("Voting Page: User ID {$user->id} - Role: {$user->role} - Signatory: {$user->authorized_signatory_with_fallback}", [
                'userId' => $user->id,
                'role' => $user->role,
                'email' => $user->email,
                'signatory' => $user->authorized_signatory_with_fallback,
                'accountNo' => $user->account_no
            ]);
        }


        $revokeOptions = $this->formatRevokeOptions($proxyBod, $proxyAmendment);

        $onlineVoting = $this->checkVotingDay('Stockholder Online Voting');
        $proxyVoting = $this->checkVotingDay('Proxy Voting');

        $terms = $this->termsAndCondition($fullName);
        $issuedProxy = $this->getIssuedProxyCount($proxyBod, $proxyAmendment);


        $param = array_merge([

            'btnDisableOnlineVoting' => $this->stockholderOnlineActive === true ? '' : 'disabled',
            'btnDisableProxyVoting' => $this->proxyVotingActive === true ? '' : 'disabled',

            'stockholderOnlineTT' => $onlineVoting,
            'proxyVotingTT' => $proxyVoting,

            'stockholderOnlineTC' => $terms['termsAndConditionsOnline'],
            'proxyVotingTC' => $terms['termsAndConditionsProxy'],

            'issuedProxy' => $issuedProxy,
            'userInitials' => $this->generateUserInitials($user),
            'amendmentEnabled' => $amendmentEnabled,
            'bodEnabled' => $bodEnabled

        ], $revokeOptions);

        Log::info("Voting Page: Successfully loaded", ["data" => $param]);

        ActivityController::log([
            'activityCode' => '00132',
            'remarks' => 'Accessed voting page',
            'data' => json_encode($param),
            'userId' => Auth::user()->id,
            'email' => Auth::user()->email,
            'accountNo' => Auth::user()->account_no
        ]);

        return view('user.voting-page', $param);
    }

    private function getIssuedProxyCount(array $proxyBod, array $proxyAmendment): int
    {

        return count($proxyBod) + count($proxyAmendment);
    }


    /**
     * Get the list of BOD proxies assigned to the user that have not been used and are not delinquent.
     * 
     */
    private function getProxyBod(array $userIds, bool $bodEnabled): array
    {

        $proxy =  ProxyBoardOfDirector::whereHas('stockholderAccount', function ($query) use ($userIds) {
            $query->whereIn('userId', $userIds)
                ->where('isDelinquent', false)
            ;
        })
            ->whereDoesntHave('usedAccount')
            ->get()
            ->toArray();

        return $bodEnabled ?  $proxy : [];
    }

    /**
     * Get the list of amendment proxies assigned to the user that have not been used and are not delinquent.
     */
    private function getProxyAmendment(array $userIds, bool $amendmentEnabled): array
    {

        $proxy =  ProxyAmendment::whereHas('stockholderAccount', function ($query) use ($userIds) {
            $query->whereIn('userId', $userIds)
                ->where('isDelinquent', false)
            ;
        })
            ->whereDoesntHave('usedAccount')
            ->get()
            ->toArray();

        return $amendmentEnabled ?  $proxy : [];
    }


    /**
     * Check if both amendment and BOD modules are disabled in the configuration settings. If both are disabled, log a warning and throw a ValidationErrorException indicating that voting is unavailable.
     */
    private function checkConfig(bool $bodEnabled, bool $amendmentEnabled)
    {
        if ($bodEnabled === false && $amendmentEnabled === false) {
            Log::warning("Voting Page: Both amendment and BOD modules are disabled. User will not be able to vote.");
            throw new ValidationErrorException('Voting is currently unavailable as both amendment and BOD voting are disabled in settings. Please contact admin.');
        }
    }

    /**
     * Generate the terms and conditions for online and proxy voting by replacing the placeholder [voter_name] with the actual full name of the voter. The terms and conditions are retrieved from the configuration settings.
     *  
     */

    private function termsAndCondition(string $fullName): array
    {

        $settings = $this->getConfigSettings();

        return [
            'termsAndConditionsOnline' => str_ireplace('[voter_name]', $fullName, $settings['terms_and_conditions_online'] ?? ''),
            'termsAndConditionsProxy' => str_ireplace('[voter_name]', $fullName, $settings['terms_and_conditions_proxy'] ?? '')
        ];
    }

    private function generateUserInitials(User $user): string
    {

        // Generate user initials for avatar
        $userFullName = $user->authorized_signatory ?? '';
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
            $none = true; // Always enable "none" if user has any proxy
        }


        if ($hasAmendmentProxy) {
            $amendment = true;
        }

        // Enable BOD option if user has BOD proxy
        if ($hasBodProxy) {
            $bod = true;
        }

        // Enable "all/both" option only if user has both types of proxies
        if ($hasAmendmentProxy && $hasBodProxy) {
            $all = true;
        }

        $options = [
            'amendment' => $amendment,
            'bod' => $bod,
            'all' => $all,
            'none' => $none
        ];

        Log::info("Voting Page: Revoke options determined", [
            'proxyBodCount' => count($proxyBod),
            'proxyAmendmentCount' => count($proxyAmendment),
            'options' => $options
        ]);

        return $options;
    }











    /**
     * Check if the current date is within the voting period for the specified voting type and return appropriate messages.
     * @param string $votingType The type of voting to check (e.g., 'Stockholder Online Voting' or 'Proxy Voting')
     * @return string A message indicating the voting status or period
     * @throws Exception If an invalid voting type is provided
     */
    private function checkVotingDay(string $votingType): string
    {

        $votingType = UtilityService::validateVotingType($votingType);

        $currentDateTime = Carbon::now();
        $settings = $this->getConfigSettings();

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


    public static function isElectionOngoing(string $votingType): bool|string
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
            return "The {$votingType} period will begin at {$formattedStartDate} and continue until {$formattedEndDate}.";
        }

        if ($currentDateTime->gt($endDate)) {
            Log::info("{$votingType}: Voting has ended.");
            return "The period for {$votingType} ended at {$formattedEndDate}.";
        }

        Log::warning("{$votingType}: Unexpected state in {$votingType} period check.");

        return false;
    }

    /**
     * Generate a user-friendly message indicating the voting period status based on the current date and the configured start and end dates for the specified voting type.
     * @param Carbon $currentDateTime The current date and time
     * @param Carbon $startDate The start date and time of the voting period
     * @param Carbon $endDate The end date and time of the voting period
     * @param string $votingType The type of voting (e.g., 'Stockholder Online Voting' or 'Proxy Voting')
     * @return string A message indicating whether the voting period has not started, has ended, or is currently ongoing
     * @throws Exception If an invalid voting type is provided
     */
    private function generateVotingPeriodMessage(Carbon $currentDateTime, Carbon $startDate, Carbon $endDate, string $votingType): string
    {
        $dateFormat = 'F j, Y, \a\t g:i A';
        $formattedStartDate = $startDate->format($dateFormat);
        $formattedEndDate = $endDate->format($dateFormat);

        if ($currentDateTime->lt($startDate)) {
            Log::info("Voting Page: {$votingType} has not started yet.");
            return "The {$votingType} period runs from {$formattedStartDate} to {$formattedEndDate}.";
        }

        if ($currentDateTime->gt($endDate)) {
            Log::info("Voting Page: {$votingType} has ended.");
            return "The period for {$votingType} ended at {$formattedEndDate}.";
        }

        Log::warning("Voting Page: Unexpected state in {$votingType} period check.");

        // This shouldn't happen if called correctly, but just in case. This has been handled from the calling controller.
        return "Voting Page: The {$votingType} period runs from {$formattedStartDate} to {$formattedEndDate}.";
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

        $amendmentEnableed = (int) $settings['amendment_enabled'] === 1;
        $bodEnabled = (int) $settings['bod_module_enabled'] === 1;

        if ($amendmentEnableed) {
            $amendment = Amendment::where('isActive', true)->count();
            if ($amendment === 0) {
                Log::error('Amendment voting is enabled in settings but no active amendment found.');
                throw new ValidationErrorException('Amendment voting is enabled in settings but no active amendment found. Please contact admin.');
            }
        }

        if ($bodEnabled) {
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

        if (!$amendmentEnableed && !$bodEnabled) {
            Log::error('Both amendment and BOD voting are disabled in settings. No voting items to validate.');
            throw new ValidationErrorException('Voting is currently unavailable as both amendment and BOD voting are disabled in settings. Please contact admin.');
        }
    }


    /**
     * Check if the user can vote based on available votes, voting type, and whether they have already submitted a ballot.
     * 
     * 
     */
    public static function checkIfUserCanVote(array $availableVotes, string $votingType, bool $hasSubmittedBallot): bool
    {

        UtilityService::validateVotingType($votingType);

        Log::info("{$votingType}: Checking if user can vote");

        $bodVotes = count($availableVotes['bod']);
        $amendmentVotes = count($availableVotes['amendment']);


        if ($bodVotes === 0 && $amendmentVotes === 0) {

            if ($hasSubmittedBallot) {
                Log::info("{$votingType}: User has already submitted a ballot and has no available votes left.");
                $msg = $votingType === 'Stockholder Online Voting' ? "You have already submitted your ballot for {$votingType} and have no available votes left." : "You have already submitted your ballot for {$votingType} and have no available votes left.";
                ActivityController::log([
                    'activityCode' => $votingType === 'Stockholder Online Voting' ? '00090' : '00091',
                    'remarks' => $msg,
                    'userId' => Auth::user()->id,
                    'email' => Auth::user()->email,
                    'accountNo' => Auth::user()->account_no
                ]);
                throw new ValidationErrorException("You have already submitted your ballot for {$votingType} and have no available votes remaining.");
            }


            $msg = "No votes are available for your {$votingType} account.";
            Log::info("{$votingType}: No available votes found.", [
                'votingType' => $votingType,
                'bodVotes' => $bodVotes,
                'amendmentVotes' => $amendmentVotes
            ]);

            $activityCode = $votingType === 'Stockholder Online Voting' ? '00090' : '00091';
            ActivityController::log([
                'activityCode' => $activityCode,
                'remarks' => $msg,
                'userId' => Auth::user()->id,
                'email' => Auth::user()->email,
                'accountNo' => Auth::user()->account_no
            ]);
            throw new ValidationErrorException($msg);
        }

        Log::debug("{$votingType}: User has available votes.", [
            'bodVotes' => $bodVotes,
            'amendmentVotes' => $amendmentVotes
        ]);

        return true;
    }


    public static function generateBallot(string $votingType): array
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

    public function processUserSubmittedData(Request $request, Ballot $ballotInfo): array
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        Log::info("{$votingType}: Processing user-submitted voting data in processUserSubmittedData for ballot ID: " . $request->ballotId, [
            'ballotId' => $request->ballotId,
            'ballotType' => $ballotInfo->ballotType,
            'ip' => $request->ip(),
        ]);

        $amendmentSummary =  $this->processAmendmentSummary($request->amendment, $ballotInfo);
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

    private function processBodSummary(?array $bod, Ballot $ballotInfo): array
    {


        $bodModuleEnabled = ConfigService::isBodEnabled();
        if ($bodModuleEnabled === false) {
            Log::info("BOD voting is disabled in settings. Skipping BOD summary processing.");
            return [];
        }

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


    private function processAmendmentSummary(array $amendments, Ballot $ballotInfo): array
    {

        $amendmentEnabled = ConfigService::isAmendmentEnabled();

        if ($amendmentEnabled === false) {
            Log::info("Amendment voting is disabled in settings. Skipping amendment summary processing.");
            return [];
        }

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




    private function processAgendaSummary(?array $agendas, Ballot $ballotInfo): array
    {

        $bodModuleEnabled = ConfigService::isBodEnabled();

        if ($bodModuleEnabled === false) {
            Log::info("BOD voting is disabled in settings. Skipping agenda summary processing.");
            return [];
        }

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

    /**
     * Fetch ballot information based on the provided ballot ID and voting type. Validates the voting type, checks if the ballot exists, and ensures it has not been submitted yet.
     * @param int $ballotId The ID of the ballot
     * @return Ballot The ballot information retrieved from the database
     * @throws ValidationErrorException If the ballot is not found, has an invalid type, or has already been submitted
     */
    public function ballotInfo(int $ballotId): Ballot
    {


        $ballotInfo = Ballot::where('ballotId', $ballotId)
            ->where('ballotType', $this->ballotType)
            ->where('isViewed', 1)
            ->where('createdBy', Auth::user()->id)
            ->first();

        if (!$ballotInfo) {
            Log::error("{$this->votingType}: Ballot not found or invalid ballot type for ballot ID " . $ballotId, [
                'ballotId' => $ballotId,
            ]);
            throw new  ValidationErrorException('Ballot not found. Please contact support if you believe this is an error.');
        }

        if ($ballotInfo->isSubmitted === 1) {
            Log::error("{$this->votingType}: Ballot has already been submitted for ballot ID " . $ballotId, [
                'ballotId' => $ballotId,
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

    /**
     * Check for unused votes and log the activity if any.
     * @param Ballot $ballotInfo The ballot information
     * @param int $totalVotesSubmitted The total number of votes submitted by the user
     * @param int $unusedVotes The number of unused votes
     * @return string|bool A message indicating unused votes or true if no unused votes
     */
    public function checkUnusedVotes(Ballot $ballotInfo, int $totalVotesSubmitted, int $unusedVotes): string|bool
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
                'userId' => Auth::user()->id,
                'email' => Auth::user()->email,
                'accountNo' => Auth::user()->account_no
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


    public function checkExceedVotes(array $userSubmittedData, Ballot $ballotInfo, int $totalVotesSubmitted): void
    {

        if (ConfigService::isBodEnabled() === false) {
            Log::info("BOD voting is disabled in settings. Skipping exceed votes check.");
            return;
        }

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


    private function recordExceedVotes(Ballot $ballotInfo, array $userSubmittedData, string $message): BallotConfirmation
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


    /**
     * Check if the provided email has already been used to submit a ballot for the specified voting type.
     * @param string $email The email address to check
     * @param string $votingType The type of voting ('Stockholder Online Voting' or 'Proxy Voting')
     * @return bool True if the email has already been used to submit a ballot, false otherwise
     * @throws Exception If an invalid voting type is provided
     */
    public static function hasSubmittedBallot(string $email, string $votingType): bool
    {

        if (empty($email)) {
            throw new Exception('Email is required to check ballot submission.');
        }

        UtilityService::validateVotingType($votingType);

        $ballotType = $votingType === 'Stockholder Online Voting' ? 'person' : 'proxy';
        $ballot = Ballot::where('email', $email)->where('ballotType', $ballotType)->where('isSubmitted', true);

        return $ballot->exists();
    }

    /**
     * Save attendance records for available votes
     * @param array $availableVotes Array containing available votes categorized by type (e.g., 'bod', 'amendment')
     * @param Ballot $ballot The ballot instance for which attendance is being recorded
     * @param string $votingType Type of voting ('Stockholder Online Voting' or 'Proxy Voting')
     * @throws Exception if an invalid voting type is provided or if attendance creation fails
     */

    public static function saveAttendance(array $availableVotes, Ballot $ballot, string $votingType)
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


    /**
     * Generate a summary information message based on the user's voting status and unused votes.
     * @param string|bool $usedAllVotes Indicates whether the user has used all their votes (true if all votes are used, false otherwise)
     * @param int $unusedVotes The number of unused votes remaining
     * @param bool $confirmMessage Whether to include a confirmation message in the output (default is true)
     * @return string A summary information message for the user regarding their voting status and unused votes
     */
    public static function generateSummaryInfoMessage(string|bool $usedAllVotes, int $unusedVotes, bool $confirmMessage = true): string
    {
        if ($usedAllVotes !== true) {

            $voteText = $unusedVotes == 1 ? 'unused vote' : 'unused votes';

            return $confirmMessage ?
                "Once confirmed, you will no longer be allowed to make any changes to this ballot. All of your $unusedVotes $voteText will be voided in this submission and will not be counted. You will also receive an email confirming that your votes have been submitted to the Club." :
                "Once confirmed, you will no longer be allowed to make any changes to this ballot. All of your $unusedVotes $voteText will be voided in this submission and will not be counted.";
        }


        return $confirmMessage ?
            "Once confirmed, you will no longer be allowed to make any changes to this ballot. You will also receive an email confirming that your votes have been submitted to the Club." :
            "Once confirmed, you will no longer be allowed to make any changes to this ballot.";
    }


    /**
     * Update the status of a ballot after submission, including the number of votes cast and unused votes.
     * @param Ballot $ballotInfo The ballot information to be updated
     * @param BallotConfirmation $ballotConfirmation The confirmation information related to the ballot submission
     * @throws Exception If the confirmation data is empty or invalid
     */
    public function updateBallotStatus(Ballot $ballotInfo, BallotConfirmation $ballotConfirmation)
    {

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $confirmationData = json_decode($ballotConfirmation->data);

        if (empty($confirmationData)) {
            throw new Exception("Confirmation data is empty for confirmation ID: " . $ballotConfirmation->confirmationId);
        }

        $totalVotesSubmitted = collect($confirmationData->bod)->sum(function ($bod) {
            return isset($bod->vote) && is_numeric($bod->vote) ? (int)$bod->vote : 0;
        });


        $unusedVotesBod = ($ballotInfo->availableVotesBod ?? 0) - $totalVotesSubmitted;

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


    public function createBallotBodDetails(BallotConfirmation $confirmation, Ballot $ballotInfo)
    {


        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $candidateData = $this->processBodForCreation($confirmation, $ballotInfo);



        if (!empty($candidateData)) {
            $ballotDetail = BallotDetail::insert($candidateData);

            Log::info("{$votingType}: Ballot details inserted successfully.", ['votingType' => $votingType, 'ballotDetails' => $candidateData]);

            return $ballotDetail;
        }

        Log::info("{$votingType}: No ballot details to insert.", ['votingType' => $votingType]);

        return false;
    }


    public function createBallotAgendaDetails(BallotConfirmation $confirmation, Ballot $ballotInfo)
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


    public function createBallotAmendmentDetails(BallotConfirmation $confirmation, Ballot $ballotInfo)
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

        $ballotService = new BallotService();
        $data = json_decode($ballotConfirmation->data, true);
        $agendaData = $data['agenda'] ?? [];

        $agendaProcessedData = [];

        foreach ($agendaData as $agenda) {

            $ballotService->validateAgendaForm($agenda["vote"], $ballotInfo);

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

    private function processAmendmentForCreation(BallotConfirmation $ballotConfirmation, Ballot $ballotInfo): array
    {
        if (ConfigService::isAmendmentEnabled() === false) {
            Log::info("Amendment voting is disabled in settings. Skipping amendment processing for ballot ID " . $ballotConfirmation->ballotId);
            return [];
        }

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        if ($ballotInfo->availableVotesAmendment === 0) {
            return [];
        }

        $ballotService = new BallotService();
        $data = json_decode($ballotConfirmation->data, true);
        $amendmentData = $data['amendment'];

        $amendmentProcessedData = [];

        foreach ($amendmentData as $amendment) {

            $ballotService->validateAmendmentForm($amendment["vote"], $ballotInfo);

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


    /**
     * Process Board of Directors (BOD) votes for creation based on the ballot confirmation and ballot information. Validates the BOD data, checks for available votes, and prepares the candidate data for insertion into the database.
     * @param BallotConfirmation $ballotConfirmation The ballot confirmation containing the submitted data
     * @param Ballot $ballotInfo The ballot information containing available votes and other details
     * @return array An array of candidate data to be inserted into the database for BOD votes
     *          [
     *              'vote' => (int) The number of votes for the candidate,
     *              'candidateId' => (int) The ID of the candidate,
     *              'ip' => (string) The IP address of the user submitting the votes,
     *              'ballotId' => (int) The ID of the ballot,
     *              'createdBy' => (int) The ID of the user who created the vote,
     *          ]
     * 
     * @throws Exception If any candidate has an invalid vote count
     * 
     * 
     */
    private function processBodForCreation(BallotConfirmation $ballotConfirmation, Ballot $ballotInfo): array
    {
        if (!ConfigService::isBodEnabled()) {
            Log::info("BOD voting is disabled in settings. Skipping BOD processing for ballot ID " . $ballotConfirmation->ballotId);
            return [];
        }

        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        if ($ballotInfo->availableVotesBod === 0) {
            Log::info("{$votingType}: No available votes for Board of Directors for ballot ID " . $ballotConfirmation->ballotId, [
                'ballotId' => $ballotConfirmation->ballotId,
                'confirmationId' => $ballotConfirmation->confirmationId,
            ]);
            return [];
        }

        $data = json_decode($ballotConfirmation->data, true);

        $bodData = $data['bod'] ?? [];
        $userIp = request()->ip();
        $userId = Auth::id();
        $candidateData = [];

        foreach ($bodData as $candidate) {
            $vote = (int)($candidate['vote'] ?? 0);

            // This should be not happening since we are validating the form before this step. But just in case, we will throw an exception.
            if ($vote <= 0) {
                throw new Exception("{$votingType}: Invalid vote count for candidate ID {$candidate['candidateId']}. Votes must be at least 1.");
            }

            $candidateData[] = [
                'vote' => $vote,
                'candidateId' => $candidate['candidateId'],
                'ip' => $userIp,
                'ballotId' => $ballotConfirmation->ballotId,
                'createdBy' => $userId,
            ];
        }

        Log::info("{$votingType}: Processed " . count($candidateData) . " Board of Directors votes for creation for ballot ID " . $ballotConfirmation->ballotId, [
            'ballotId' => $ballotConfirmation->ballotId,
            'confirmationId' => $ballotConfirmation->confirmationId,
            'processedVotes' => count($candidateData),
            'countedVotes' => array_sum(array_column($candidateData, 'vote')),
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
