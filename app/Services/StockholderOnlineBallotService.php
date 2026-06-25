<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Http\Requests\StoreStockholderOnlineBallotRequest;
use App\Models\Ballot;
use App\Models\StockholderAccount;
use App\Models\User;
use Exception;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;
use App\Services\ConfirmationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Amendment;
use App\Models\Agenda;
use App\Models\Configuration;
use App\Services\BallotService;
use App\Http\Requests\SummaryStockholderOnlineRequest;
use App\Http\Requests\SubmitStockholderOnlineRequest;




class StockholderOnlineBallotService
{


    public BallotService $ballotService;

    public function __construct()
    {
        $this->ballotService = new BallotService();
    }
    public function show(Request $request, string $id)
    {

        try {

            Log::info("Stockholder Online Voting: Loading Stockholder Online Ballot for ID {$id}.", [
                'ballotId' => $id,
            ]);

            $ballotInfo = Ballot::where('ballotId', $id)
                ->where('createdBy', Auth::id())
                ->where('isSubmitted', false)
                ->where('ballotType', 'person')
                ->where('isViewed', false)
                ->first();

            if ($ballotInfo === null) {

                Log::info("Stockholder Online Voting: Ballot has already been viewed or submitted. Redirecting to voting page.", [
                    'ballotId' => $id,
                ]);

                ActivityController::log([
                    'activityCode' => '00090',
                    'remarks' => 'Attempted to access a ballot that has already been viewed or submitted. Redirecting to voting page.',
                    'ballotId' => $id,
                    'userId' => Auth::id(),
                ]);

                return redirect('user/vote');
            }



            Log::info("Stockholder Online Voting: Loading user information.", [
                'ballotId' => $id,
            ]);

            $userInfo = User::with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder')->findOrFail(Auth::id());

            $this->markBallotAsViewed($ballotInfo);

            $candidates = Candidate::where('isActive', 1)->orderBy('type', 'asc')->orderBy('lastName', 'asc')->orderBy('type', 'asc')->get();
            $amendment = Amendment::where('isActive', 1)->get();
            $agenda = Agenda::where('isActive', 1)->get();


            $amendmentForm = BallotService::generateAmendmentForm($amendment, $ballotInfo->availableVotesAmendment);
            $agendaForm = BallotService::generateAgendaForm($agenda, $ballotInfo->availableVotesBod);


            Log::info("Stockholder Online Voting: Successfully displayed ballot information.", [
                'ballotId' => $id,
            ]);


            return view('user.stockholder-onlilne-form', [

                'ballotId' => $ballotInfo->ballotId,
                'ballotNo' => $ballotInfo->ballotNo,
                'userInfo' => $userInfo,
                'availableVotesBod' => $ballotInfo->availableVotesBod,
                'availableVotesAmendment' => $ballotInfo->availableVotesAmendment,
                'availableSharesBod' => count(json_decode($ballotInfo->availableBodAccounts)),
                'availableSharesAmendment' => count(json_decode($ballotInfo->availableAmendmentAccounts)),
                'candidates' => $candidates,
                'amendmentForm'    => $amendmentForm,
                'agendaForm'    => $agendaForm,
                'configuration'  => Configuration::all()->toArray(),
                'amendmentEnabled'  => ConfigService::getConfig('amendment_enabled') === '1',
                'bodEnabled'  => ConfigService::getConfig('bod_module_enabled') === '1'

            ]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error displaying Stockholder Online Ballot for ID {$id}.");

            return view('errors.500');
        }
    }

    private function markBallotAsViewed(Ballot $ballotInfo): Ballot
    {

        $ballotInfo->isViewed = true;
        $ballotInfo->save();

        Log::info("Stockholder Online Voting: Ballot ID {$ballotInfo->ballotId} marked as viewed.", [
            'ballotId' => $ballotInfo->ballotId,
        ]);

        return $ballotInfo;
    }

    public function summary(SummaryStockholderOnlineRequest $request)
    {

        try {

            Log::info('Stockholder Online Voting: Processing summary for ballot ID ' . $request->ballotId, [
                'ballotId' => $request->ballotId,
            ]);

            $this->ensureElectionIsOngoing('summary', $request->ballotId, $request->confirmationId);



            $ballotInfo = $this->ballotService->ballotInfo($request, 'Stockholder Online Voting');

            $userSubmittedData = $this->ballotService->processUserSubmittedData($request, $ballotInfo);

            $this->ensureAvailableVotesAreUnchanged($ballotInfo, $userSubmittedData);

            $totalVotesSubmitted = collect($userSubmittedData['bod'])->sum('vote');
            $unusedVotes = ConfigService::isBodEnabled() ? $ballotInfo->availableVotesBod - $totalVotesSubmitted : 0;

            $this->ballotService->checkExceedVotes($userSubmittedData, $ballotInfo, $totalVotesSubmitted);

            $overallVotesStatus = $this->ballotService->checkUnusedVotes($ballotInfo, $totalVotesSubmitted, $unusedVotes);

            $message = $this->ballotService->generateSummaryInfoMessage($overallVotesStatus, $unusedVotes, true);
            $infoMessage = $this->ballotService->generateSummaryInfoMessage($overallVotesStatus, $unusedVotes, false);


            DB::beginTransaction();
            $confirmationService = new ConfirmationService();


            $ballotConfirmation = $confirmationService->store($ballotInfo, $userSubmittedData, true, $message);


            ActivityController::log([
                'activityCode' => '00071',
                'remarks' => $message,
                'confirmationId' => $ballotConfirmation->confirmationId,
                'ballotId' => $request->ballotId,
                'userId' => Auth::id(),
                'email' => Auth::user()->email,
                'accountNo' => Auth::user()->account_no,
            ]);

            Log::info('Stockholder Online Voting: Summary processed successfully for ballot ID ' . $request->ballotId, [
                'ballotId' => $request->ballotId,
                'confirmationId' => $ballotConfirmation->confirmationId,
            ]);

            DB::commit();

            return response()->json([
                'bod' => $userSubmittedData['bod'],
                'amendment' => $userSubmittedData['amendment'],
                'agenda' => $userSubmittedData['agenda'],
                'bodVotes' => $totalVotesSubmitted,
                'unusedVotes' => $unusedVotes,
                'confirmationId' => $ballotConfirmation->confirmationId,
                'info' => $infoMessage,
                'message' => $message
            ]);
        } catch (ValidationErrorException $e) {

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error processing Stockholder Online Voting summary for ballot ID " . $request->ballotId, ['ballotId' => $request->ballotId, 'confirmationId' => $request->confirmationId]);

            if ($e instanceof ValidationErrorException) {
                return response()->json(['message' => $e->getMessage()], 400);
            }

            return response()->json([], 500);
        }
    }

    private function ensureAvailableVotesAreUnchanged(Ballot $ballotInfo, array $userSubmittedData): void
    {
        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $recordChanged = $this->ballotService->checkAccountChanges($ballotInfo);

        if ($recordChanged === true) {

            $message = "{$votingType}: Your available votes have changed since you generated your ballot. Please reload the page to ensure your vote is accurate and up to date.";

            $confirmationnService = new ConfirmationService();

            $ballotConfirmation = $confirmationnService->createAvailableVoteChangeRecord($ballotInfo, $userSubmittedData);
            $this->ballotService->createAvailableVoteChangeActivityLog($ballotInfo, $ballotConfirmation, $message);
            throw new ValidationErrorException($message);
        }
    }


    /**
     * Get available accounts for the authenticated user, categorized by revoke option.
     */
    public function getAvailableAccounts()
    {
        $userInfo = Auth::user();

        $availableAccounts['bod'] =  $this->getAvailableVotes('bod', $userInfo);
        $availableAccounts['amendment'] = $this->getAvailableVotes('amendment', $userInfo);
        $availableAccounts['both'] =  $this->getAvailableVotes('both', $userInfo);
        $availableAccounts['none'] =  $this->getAvailableVotes('none', $userInfo);

        return $availableAccounts;
    }



    /**
     * Get available votes for a user based on the revoke option.
     */
    public function getAvailableVotes(string $revoke, User $userInfo)
    {

        $accountIds = (new OnlineAccountService())->getAccounts($userInfo->email, true);

        switch ($revoke) {

            case 'bod':
                $availableVotesBod = StockholderAccount::whereIn('userId', $accountIds)
                    ->where('isDelinquent', 0)
                    ->whereDoesntHave('usedBodAccount')
                    ->pluck('accountId');

                $availableVotesAmendment = StockholderAccount::whereIn('userId', $accountIds)
                    ->where('isDelinquent', 0)
                    ->whereDoesntHave('proxyAmendment')
                    ->whereDoesntHave('usedAmendmentAccount')
                    ->pluck('accountId');

                break;

            case 'amendment':

                $availableVotesBod = StockholderAccount::whereIn('userId', $accountIds)
                    ->where('isDelinquent', 0)
                    ->whereDoesntHave('proxyBoard')
                    ->whereDoesntHave('usedBodAccount')
                    ->pluck('accountId');

                $availableVotesAmendment = StockholderAccount::whereIn('userId', $accountIds)
                    ->where('isDelinquent', 0)
                    ->whereDoesntHave('usedAmendmentAccount')
                    ->pluck('accountId');

                break;


            case 'both':
                $availableVotesBod = StockholderAccount::whereIn('userId', $accountIds)
                    ->where('isDelinquent', 0)
                    ->whereDoesntHave('usedBodAccount')
                    ->pluck('accountId');

                $availableVotesAmendment = StockholderAccount::whereIn('userId', $accountIds)
                    ->where('isDelinquent', 0)
                    ->whereDoesntHave('usedAmendmentAccount')
                    ->pluck('accountId');


                break;

            case 'none':
                $availableVotesBod = StockholderAccount::whereIn('userId', $accountIds)
                    ->where('isDelinquent', 0)
                    ->whereDoesntHave('proxyBoard')
                    ->whereDoesntHave('usedBodAccount')
                    ->pluck('accountId');

                $availableVotesAmendment = StockholderAccount::whereIn('userId', $accountIds)
                    ->where('isDelinquent', 0)
                    ->whereDoesntHave('proxyAmendment')
                    ->whereDoesntHave('usedAmendmentAccount')
                    ->pluck('accountId');


                break;
            default:
                Log::error("Stockholder Online Voting: Invalid revoke option provided for stockholder user: " . $userInfo->id, ['revoke' => $revoke]);
                throw new Exception("Invalid revoke option provided: {$revoke}");
                break;
        }


        $bodEnabled = ConfigService::getConfig("bod_module_enabled") === '1';
        $amendmentEnabled = ConfigService::getConfig("amendment_enabled") === '1';


        return array(
            'bod' => $bodEnabled ? $availableVotesBod->toArray() : [],
            'amendment' => $amendmentEnabled ? $availableVotesAmendment->toArray() : []
        );
    }

    public function submit(SubmitStockholderOnlineRequest $request)
    {

        Log::info('Stockholder Online Voting: Submitting ballot ID ' . $request->ballotId, [
            'ballotId' => $request->ballotId,
            'confirmationId' => $request->confirmationId
        ]);

        $this->checkIfUserIsAuthorizedVoter(Auth::user());

        $this->ensureElectionIsOngoing('submit', $request->ballotId, $request->confirmationId);


        $ballotInfo = BallotService::ballotInfo($request, 'Stockholder Online Voting');
        $confirmation = ConfirmationService::ensureBallotConfirmationIsValid($ballotInfo, $request);

        $this->ensureAvailableVotesAreUnchanged($ballotInfo, json_decode($confirmation->data, true));


        DB::beginTransaction();

        $this->ballotService->updateBallotStatus($ballotInfo, $confirmation);


        $this->ballotService->createBallotDetails($confirmation, $ballotInfo);
        $this->ballotService->createBallotAgendaDetails($confirmation, $ballotInfo);
        $this->ballotService->createBallotAmendmentDetails($confirmation, $ballotInfo);

        $this->ballotService->insertUsedAccounts($confirmation);

        ActivityController::log(['activityCode' => '00094', 'ballotId' => $ballotInfo->ballotId, 'confirmationId' => $confirmation->confirmationId, 'userId' => Auth::id()]);


        DB::commit();

        if (app()->environment('production')) {
            BallotService::sendVoteConfirmation(Auth::user()->email, 'Stockholder Online Voting', $confirmation->id, $ballotInfo);
        }


        return response()->json([
            'message' => 'Thank you for your participation in the election.',
            'ballotId' => $ballotInfo->ballotId,
            'confirmationId' => $confirmation->confirmationId
        ], 200);
    }


    private static function checkIfUserIsAuthorizedVoter(User $userInfo): bool
    {

        return !User::where('email', $userInfo->email)
            ->where('role', 'non-member')
            ->exists();
    }

    public function store(StoreStockholderOnlineBallotRequest $request)
    {
        try {


            $this->ensureElectionIsOngoing('store');
            BallotService::validateVotingItems();


            $userInfo = User::with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder')->findOrFail(Auth::id());

            $hasSubmittedBallot = BallotService::hasSubmittedBallot($userInfo->email, 'Stockholder Online Voting');

            $availableVotes =  $this->getAvailableVotes($request->revoke, $userInfo);
            $availableAccounts = $this->getAvailableAccounts();


            BallotService::checkIfUserCanVote($availableVotes, "Stockholder Online Voting", $hasSubmittedBallot);

            DB::beginTransaction();

            $ballotInfo = $this->saveBallot($userInfo, $availableVotes, $availableAccounts);


            BallotService::saveAttendance($availableVotes, $ballotInfo, "Stockholder Online Voting");

            Log::info("Stockholder Online Voting: Stockholder Online Ballot created successfully.", [
                'ballotId' => $ballotInfo->ballotId,
            ]);

            ActivityController::log([
                'activityCode' => '00047',
                'ballotId' => $ballotInfo->ballotId,
                'userId' => $userInfo->id,
                'email' => $userInfo->email,
                'accountNo' => $userInfo->account_no,
            ]);

            DB::commit();

            return response()->json(['ballotId' => $ballotInfo->ballotId], 200);
        } catch (ValidationErrorException $e) {

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error creating Stockholder Online Ballot");

            return response()->json([], 500);
        }
    }

    private function ensureElectionIsOngoing(string $action,  $ballotId = null, $confirmationId = null)
    {

        if (!in_array($action, ['store', 'summary', 'submit'])) {
            Log::error("Invalid action specified for election status check: " . $action);
            throw new ValidationErrorException("Invalid action specified for election status check.");
        }

        $electionDataStatus = BallotService::isElectionOngoing("Stockholder Online Voting");

        if ($electionDataStatus !== true) {

            ActivityController::log([
                'activityCode' => '00090',
                'remarks' => $electionDataStatus . ' Action: ' . $action,
                'ballotId' => $ballotId,
                'confirmationId' => $confirmationId,
                'userId' => Auth::id(),
                'email' => Auth::user()->email,
                'accountNo' => Auth::user()->account_no,
            ]);

            throw new ValidationErrorException($electionDataStatus);
        }
    }



    private function saveBallot(User $userInfo, array $availableVotes, array $availableAccounts): Ballot
    {

        $votesPerShare = ConfigService::getConfig("votes_per_share");
        $ballot = BallotService::generateBallot("Stockholder Online Voting");

        $request = app('request');


        $ballot  = Ballot::create([
            'ballotId'                  => $ballot['ballotId'],
            'ballotNo'                  => $ballot['ballotNo'],
            'ballotKey'                 => $ballot['ballotNo'] . '-person',

            'email'                     => Auth::user()->email,
            'ip'                        => $request->ip(),
            'availableBodAccounts'       => json_encode($availableVotes['bod']),
            'availableAmendmentAccounts' => json_encode($availableVotes['amendment']),
            'availableAccounts'         => json_encode($availableAccounts),
            'ballotType'                => 'person',
            // 'authorizedVoter'           => $authorizedVoter,
            'role'                      => $userInfo->role,
            'availableVotesBod'         => count($availableVotes['bod']) * $votesPerShare,
            'availableVotesAmendment'   => count($availableVotes['amendment']),

            'castedVotes'               => null,
            'unusedVotesBod'            => null,
            'unusedVotesAmendment'      => null,
            'revoked'                   => $request->revoke,
            'isSubmitted'               => false,
            'isViewed'                  => false,

            'submittedAt'               => null,
            'confirmationId'            => null,
            'isActive'                  => true,
            'createdBy'                 => $userInfo->id
        ]);

        Log::info("Stockholder Online Voting: Ballot has been created", ['ballotId' => $ballot->ballotId]);

        return $ballot;
    }
}
