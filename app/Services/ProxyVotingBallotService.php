<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Models\Ballot;
use App\Models\ProxyAmendment;
use App\Models\ProxyBoardOfDirector;
use App\Models\StockholderAccount;
use App\Models\User;
use Exception;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;
use App\Services\ConfirmationService;
use Illuminate\Support\Facades\DB;



class ProxyVotingBallotService
{


    public VoteService $voteService;

    public function __construct()
    {
        $this->voteService = new VoteService();
    }

    public function summary($request)
    {
        try {

            Log::info('Proxy Voting: Processing summary for ballot ID ' . $request->ballotId, [
                'ballotId' => $request->ballotId,
            ]);

            $this->ensureElectionIsOngoing('summary', $request->ballotId, $request->confirmationId);

            $voteService = new VoteService();

            $ballotInfo = VoteService::ballotInfo($request, 'Proxy Voting');

            $userSubmittedData = $voteService->processUserSubmittedData($request, $ballotInfo);

            $this->ensureAvailableVotesAreUnchanged($ballotInfo, $userSubmittedData);

            $totalVotesSubmitted = collect($userSubmittedData['bod'])->sum('vote');
            $unusedVotes = $ballotInfo->availableVotesBod - $totalVotesSubmitted;

            $this->voteService->checkExceedVotes($userSubmittedData, $ballotInfo, $totalVotesSubmitted, $unusedVotes);

            $overallVotesStatus = $voteService->checkUnusedVotes($ballotInfo, $totalVotesSubmitted, $unusedVotes);

            $message = VoteService::generateSummaryInfoMessage($overallVotesStatus, $unusedVotes, true);
            $infoMessage = VoteService::generateSummaryInfoMessage($overallVotesStatus, $unusedVotes, false);


            DB::beginTransaction();
            $confirmationService = new ConfirmationService();
            $ballotConfirmation = $confirmationService->store($ballotInfo, $userSubmittedData, true, $message, $unusedVotes);

            ActivityController::log(['activityCode' => '00072', 'remarks' => $message, 'confirmationId' => $ballotConfirmation->confirmationId, 'ballotId' => $request->ballotId], 'userId', Auth::id());

            Log::info('Proxy Voting: Summary processed successfully for ballot ID ' . $request->ballotId, [
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
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error processing Proxy Voting summary for ballot ID " . $request->ballotId, ['ballotId' => $request->ballotId, 'confirmationId' => $request->confirmationId]);

            if ($e instanceof ValidationErrorException) {
                return response()->json(['message' => $e->getMessage()], 400);
            }

            return response()->json([], 500);
        }
    }

    private function ensureAvailableVotesAreUnchanged($ballotInfo, $userSubmittedData)
    {
        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);

        $recordChanged = $this->voteService->checkAccountChanges($ballotInfo);

        if ($recordChanged === true) {

            $message = "{$votingType}: Your available votes have changed since you generated your ballot. Please reload the page to ensure your vote is accurate and up to date.";

            $confirmationnService = new ConfirmationService();

            $ballotConfirmation = $confirmationnService->createAvailableVoteChangeRecord($ballotInfo, $userSubmittedData);
            $this->voteService->createAvailableVoteChangeActivityLog($ballotInfo, $ballotConfirmation, $message);
            throw new ValidationErrorException($message);
        }
    }





    public function getAvailableVotes($userInfo)
    {

        $user = Auth::user();

        $userInfo = User::with(['stockholder', 'stockholderAccount', 'stockholderAccount.stockholder'])
            ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
            ->findOrFail(Auth::id());


        switch ($user->role) {

            case 'stockholder':

                Log::info("Proxy Voting: Fetching available votes for stockholder.");

                $availableVotesBod = ProxyBoardOfDirector::where('assigneeId', $userInfo->id)
                    ->whereDoesntHave('usedAccount')
                    ->whereHas('stockholderAccount', function ($query) {
                        $query->where('isDelinquent', 0);
                    })
                    ->pluck('accountId');

                $availableVotesAmendment = [];

                break;

            case 'corp-rep':

                Log::info("Proxy Voting: Fetching available votes for corp-rep.");
                Log::info("Proxy Voting: Fetching all stockholder accounts belonging to corp-rep by email " . Auth::user()->email . " and account no " . Auth::user()->stockholderAccount->stockholder->accountNo);

                $corpRepAccounts = User::leftJoin('stockholder_accounts', 'stockholder_accounts.userId', '=', 'users.id')
                    ->leftJoin('stockholders', 'stockholders.stockholderId', '=', 'stockholder_accounts.stockholderId')
                    ->selectRaw('stockholder_accounts.accountId, users.id')
                    ->where('users.email', Auth::user()->email)
                    ->where('stockholders.accountNo', Auth::user()->stockholderAccount->stockholder->accountNo)
                    ->get();

                Log::info("Proxy Voting: Found " . count($corpRepAccounts) . " stockholder accounts belonging for corp-rep.", [
                    'corpRepEmail' => Auth::user()->email,
                    'accountNo' => Auth::user()->stockholderAccount->stockholder->accountNo,
                    'stockholderAccounts' => $corpRepAccounts->pluck('accountId')->toArray()
                ]);


                $assigneeAccountIds = $corpRepAccounts->pluck('id')->toArray();



                Log::info("Proxy Voting: Found " . count($assigneeAccountIds) . " stockholder accounts (User ID) for corp-rep.", [
                    'stockholderAccountUserIds' => $assigneeAccountIds
                ]);


                $availableVotesBod = ProxyBoardOfDirector::whereIn('assigneeId', $assigneeAccountIds)
                    ->whereDoesntHave('usedAccount')
                    ->whereHas('stockholderAccount', function ($query) {
                        $query->where('isDelinquent', 0);
                    })
                    ->pluck('accountId');


                $availableVotesAmendment = [];

                break;

            case 'non-member':

                Log::info("Proxy Voting: Fetching available votes for non-member.");

                $availableVotesBod = ProxyBoardOfDirector::where('assigneeId', $userInfo->id)
                    ->whereDoesntHave('usedAccount')
                    ->whereHas('stockholderAccount', function ($query) {
                        $query->where('isDelinquent', 0);
                    })
                    ->pluck('accountId');


                $availableVotesAmendment = [];


                if ($userInfo->nonMemberAccount->isGM === 1) {
                    $availableVotesAmendment = ProxyAmendment::where('assigneeId', $userInfo->id)
                        ->whereDoesntHave('usedAccount')
                        ->whereHas('stockholderAccount', function ($query) {
                            $query->where('isDelinquent', 0);
                        })
                        ->pluck('accountId');
                }

                break;
        }



        return array(
            'bod' => $availableVotesBod->toArray(),
            'amendment' => is_array($availableVotesAmendment) ? [] : $availableVotesAmendment->toArray()
        );
    }

    public function submit($request)
    {

        Log::info('Proxy Voting: Submitting ballot ID ' . $request->ballotId, [
            'ballotId' => $request->ballotId,
            'confirmationId' => $request->confirmationId
        ]);

        $this->checkIfUserIsAuthorizedVoter(Auth::user());

        $this->ensureElectionIsOngoing('submit', $request->ballotId, $request->confirmationId);


        $ballotInfo = VoteService::ballotInfo($request, 'Proxy Voting');
        $confirmation = ConfirmationService::ensureBallotConfirmationIsValid($ballotInfo, $request);

        $this->ensureAvailableVotesAreUnchanged($ballotInfo, json_decode($confirmation->data, true));


        DB::beginTransaction();

        $this->voteService->updateBallotStatus($ballotInfo, $confirmation);


        $this->voteService->createBallotDetails($confirmation, $ballotInfo);
        $this->voteService->createBallotAgendaDetails($confirmation, $ballotInfo);
        $this->voteService->createBallotAmendmentDetails($confirmation, $ballotInfo);

        $this->voteService->insertUsedAccounts($confirmation);

        ActivityController::log(['activityCode' => '00095', 'ballotId' => $ballotInfo->ballotId, 'confirmationId' => $confirmation->confirmationId, 'userId' => Auth::id()]);


        DB::commit();

        VoteService::sendVoteConfirmation(Auth::user()->email, 'Proxy Voting', $confirmation->id, $ballotInfo);

        return response()->json([
            'message' => 'Your vote has been successfully submitted.',
            'ballotId' => $ballotInfo->ballotId,
            'confirmationId' => $confirmation->confirmationId
        ], 200);
    }


    private static function checkIfUserIsAuthorizedVoter($userInfo): void
    {

        if (!in_array($userInfo->role, ['stockholder', 'corp-rep', 'non-member'])) {
            Log::error("Proxy Voting: User role " . $userInfo->role . " is not allowed to vote in Proxy Voting.", ['userId' => $userInfo->id]);
            throw new ValidationErrorException("User is not allowed to vote in Proxy Voting.");
        }
    }

    public function store($request)
    {
        try {

            $this->ensureElectionIsOngoing('store');

            Log::info("Proxy Voting: Fetching user information.");
            $userInfo = User::with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder')->findOrFail(Auth::id());

            VoteService::ensureEmailIsNotUsed($userInfo->email, 'Proxy Voting');
            VoteService::validateVotingItems();

            $authorizedVoter = $this->checkIfUserIsAuthorizedVoter($userInfo);

            Log::info("Proxy Voting: Fetching available votes.");
            $availableVotes =  $this->getAvailableVotes($userInfo);
            Log::info("Proxy Voting: Available votes fetched successfully.", ['availableVotes' => $availableVotes]);
            $availableAccounts = $availableVotes; // Store available accounts for record-keeping similar to Proxy Voting

            VoteService::checkIfUserCanVote($availableVotes, "Proxy Voting");


            DB::beginTransaction();

            $ballotInfo = $this->saveBallot($userInfo, $availableVotes, $availableAccounts, $authorizedVoter);


            VoteService::saveAttendance($availableVotes, $ballotInfo, "Proxy Voting");

            Log::info("Proxy Voting: Proxy Voting Ballot created successfully.", [
                'ballotId' => $ballotInfo->ballotId,
            ]);

            ActivityController::log(['activityCode' => '00048', 'ballotId' => $ballotInfo->ballotId, 'userId' => $userInfo->id]);

            DB::commit();

            return response()->json(['ballotId' => $ballotInfo->ballotId], 200);
        } catch (ValidationErrorException $e) {

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error creating Proxy Voting Ballot");

            return response()->json([], 500);
        }
    }

    private function ensureElectionIsOngoing($action, $ballotId = null, $confirmationId = null)
    {

        if (!in_array($action, ['store', 'summary', 'submit'])) {
            Log::error("Invalid action specified for election status check: " . $action);
            throw new ValidationErrorException("Invalid action specified for election status check.");
        }

        $electionDataStatus = VoteService::isElectionOngoing("Proxy Voting");

        if ($electionDataStatus !== true) {

            ActivityController::log([
                'activityCode' => '00091',
                'remarks' => $electionDataStatus . ' Action: ' . $action,
                'ballotId' => $ballotId,
                'confirmationId' => $confirmationId,
                'userId' => Auth::id(),
            ]);

            throw new ValidationErrorException($electionDataStatus);
        }
    }



    private function saveBallot($userInfo, $availableVotes, $availableAccounts, $authorizedVoter): Ballot
    {

        $votesPerShare = ConfigService::getConfig("votes_per_share");
        $ballot = VoteService::generateBallot("Proxy Voting");

        $request = app('request');


        $ballot  = Ballot::create([
            'ballotId'                  => $ballot['ballotId'],
            'ballotNo'                  => $ballot['ballotNo'],
            'ballotKey'                 => $ballot['ballotNo'] . '-proxy',

            'email'                     => Auth::user()->email,
            'ip'                        => $request->ip(),
            'availableBodAccounts'       => json_encode($availableVotes['bod']),
            'availableAmendmentAccounts' => json_encode($availableVotes['amendment']),
            'availableAccounts'         => json_encode($availableAccounts),
            'ballotType'                => 'proxy',
            'authorizedVoter'           => $authorizedVoter,
            'role'                      => $userInfo->role,
            'availableVotesBod'         => count($availableVotes['bod']) * $votesPerShare,
            'availableVotesAmendment'   => count($availableVotes['amendment']),

            'castedVotes'               => null,
            'unusedVotesBod'            => null,
            'unusedVotesAmendment'      => null,
            'revoked'                   => 'both',
            'isSubmitted'               => false,
            'isViewed'                  => false,

            'submittedAt'               => null,
            'confirmationId'            => null,
            'isActive'                  => true,
            'createdBy'                 => $userInfo->id
        ]);

        Log::info("Proxy Voting: Ballot has been created", ['ballotId' => $ballot->ballotId]);

        return $ballot;
    }
}
