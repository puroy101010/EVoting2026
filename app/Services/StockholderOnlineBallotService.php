<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Models\Ballot;
use App\Models\StockholderAccount;
use App\Models\User;
use Exception;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;
use App\Services\ConfirmationService;
use Illuminate\Support\Facades\DB;



class StockholderOnlineBallotService
{


    public VoteService $voteService;

    public function __construct()
    {
        $this->voteService = new VoteService();
    }

    public function summary($request)
    {

        try {



            Log::info('Stockholder Online Voting: Processing summary for ballot ID ' . $request->ballotId, [
                'ballotId' => $request->ballotId,
            ]);

            $this->ensureElectionIsOngoing('summary', $request->ballotId, $request->confirmationId);

            $voteService = new VoteService();

            $ballotInfo = VoteService::ballotInfo($request, 'Stockholder Online Voting');

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

            ActivityController::log(['activityCode' => '00071', 'remarks' => $message, 'confirmationId' => $ballotConfirmation->confirmationId, 'ballotId' => $request->ballotId], 'userId', Auth::id());

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
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error processing Stockholder Online Voting summary for ballot ID " . $request->ballotId, ['ballotId' => $request->ballotId, 'confirmationId' => $request->confirmationId]);

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


    public function getAvailableAccounts()
    {
        $userInfo = Auth::user();

        $availableAccounts['bod'] =  $this->getAvailableVotes('bod', $userInfo);
        $availableAccounts['amendment'] = $this->getAvailableVotes('amendment', $userInfo);
        $availableAccounts['both'] =  $this->getAvailableVotes('both', $userInfo);
        $availableAccounts['none'] =  $this->getAvailableVotes('none', $userInfo);

        return $availableAccounts;
    }



    public function getAvailableVotes($revoke, $userInfo)
    {

        $user = Auth::user();

        switch ($user->role) {
            case 'stockholder':

                Log::info("Stockholder Online Voting: Fetching available votes for stockholder user: " . $userInfo->id, ['revoke' => $revoke]);

                switch ($revoke) {

                    case 'bod':
                        $availableVotesBod = StockholderAccount::where('stockholderId', $user->stockholder->stockholderId)
                            ->where('isDelinquent', 0)
                            ->whereDoesntHave('usedBodAccount')
                            ->pluck('accountId');

                        $availableVotesAmendment = StockholderAccount::where('stockholderId', $user->stockholder->stockholderId)
                            ->where('isDelinquent', 0)
                            ->whereDoesntHave('proxyAmendment')
                            ->whereDoesntHave('usedAmendmentAccount')
                            ->pluck('accountId');

                        break;

                    case 'amendment':

                        $availableVotesBod = StockholderAccount::where('stockholderId', $user->stockholder->stockholderId)
                            ->where('isDelinquent', 0)
                            ->whereDoesntHave('proxyBoard')
                            ->whereDoesntHave('usedBodAccount')
                            ->pluck('accountId');

                        $availableVotesAmendment = StockholderAccount::where('stockholderId', $user->stockholder->stockholderId)
                            ->where('isDelinquent', 0)
                            ->whereDoesntHave('usedAmendmentAccount')
                            ->pluck('accountId');

                        break;


                    case 'both':
                        $availableVotesBod = StockholderAccount::where('stockholderId', $user->stockholder->stockholderId)
                            ->where('isDelinquent', 0)
                            ->whereDoesntHave('usedBodAccount')
                            ->pluck('accountId');

                        $availableVotesAmendment = StockholderAccount::where('stockholderId', $user->stockholder->stockholderId)
                            ->where('isDelinquent', 0)
                            ->whereDoesntHave('usedAmendmentAccount')
                            ->pluck('accountId');


                        break;

                    case 'none':
                        $availableVotesBod = StockholderAccount::where('stockholderId', $user->stockholder->stockholderId)
                            ->where('isDelinquent', 0)
                            ->whereDoesntHave('proxyBoard')
                            ->whereDoesntHave('usedBodAccount')
                            ->pluck('accountId');

                        $availableVotesAmendment = StockholderAccount::where('stockholderId', $user->stockholder->stockholderId)
                            ->where('isDelinquent', 0)
                            ->whereDoesntHave('proxyAmendment')
                            ->whereDoesntHave('usedAmendmentAccount')
                            ->pluck('accountId');


                        break;
                }

                Log::info("Stockholder Online Voting: Available votes for stockholder user: " . $userInfo->id, [
                    'availableVotesBod' => $availableVotesBod,
                    'availableVotesAmendment' => $availableVotesAmendment
                ]);

                break;


            case 'corp-rep':

                Log::info("Stockholder Online Voting: Fetching available votes for corp-rep user: " . $userInfo->id, ['revoke' => $revoke]);

                $accountNo = $userInfo->stockholderAccount->stockholder->accountNo;

                switch ($revoke) {
                    case 'bod':
                        $availableVotesBod = User::where('email', $userInfo->email)
                            ->join('stockholder_accounts', 'users.id', '=', 'stockholder_accounts.userId')
                            ->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0)->whereDoesntHave('usedBodAccount');
                            })
                            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                                $query->where('accountNo', $accountNo);
                            })
                            ->selectRaw('stockholder_accounts.accountId')
                            ->pluck('stockholder_accounts.accountId');


                        $availableVotesAmendment = User::where('email', $userInfo->email)
                            ->join('stockholder_accounts', 'users.id', '=', 'stockholder_accounts.userId')
                            ->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0)->whereDoesntHave('usedAmendmentAccount');
                            })
                            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                                $query->where('accountNo', $accountNo);
                            })
                            ->whereDoesntHave('stockholderAccount.proxyAmendment')
                            ->selectRaw('stockholder_accounts.accountId')
                            ->pluck('stockholder_accounts.accountId');

                        break;

                    case 'amendment':
                        $availableVotesBod = User::where('email', $userInfo->email)
                            ->join('stockholder_accounts', 'users.id', '=', 'stockholder_accounts.userId')
                            ->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0)->whereDoesntHave('usedBodAccount');
                            })
                            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                                $query->where('accountNo', $accountNo);
                            })
                            ->whereDoesntHave('stockholderAccount.proxyBoard')
                            ->selectRaw('stockholder_accounts.accountId')
                            ->pluck('stockholder_accounts.accountId');


                        $availableVotesAmendment = User::where('email', $userInfo->email)
                            ->join('stockholder_accounts', 'users.id', '=', 'stockholder_accounts.userId')
                            ->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0)->whereDoesntHave('usedAmendmentAccount');
                            })
                            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                                $query->where('accountNo', $accountNo);
                            })
                            ->selectRaw('stockholder_accounts.accountId')
                            ->pluck('stockholder_accounts.accountId');

                        break;

                    case 'both':
                        $availableVotesBod = User::where('email', $userInfo->email)
                            ->join('stockholder_accounts', 'users.id', '=', 'stockholder_accounts.userId')
                            ->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0)->whereDoesntHave('usedBodAccount');
                            })
                            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                                $query->where('accountNo', $accountNo);
                            })
                            ->selectRaw('stockholder_accounts.accountId')
                            ->pluck('stockholder_accounts.accountId');



                        $availableVotesAmendment = User::where('email', $userInfo->email)
                            ->join('stockholder_accounts', 'users.id', '=', 'stockholder_accounts.userId')
                            ->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0)->whereDoesntHave('usedAmendmentAccount');
                            })
                            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                                $query->where('accountNo', $accountNo);
                            })
                            ->selectRaw('stockholder_accounts.accountId')
                            ->pluck('stockholder_accounts.accountId');


                        break;

                    case 'none':
                        $availableVotesBod = User::where('email', $userInfo->email)
                            ->join('stockholder_accounts', 'users.id', '=', 'stockholder_accounts.userId')
                            ->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0)->whereDoesntHave('usedBodAccount');
                            })
                            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                                $query->where('accountNo', $accountNo);
                            })
                            ->whereDoesntHave('stockholderAccount.proxyBoard')
                            ->selectRaw('stockholder_accounts.accountId')
                            ->pluck('stockholder_accounts.accountId');


                        $availableVotesAmendment = User::where('email', $userInfo->email)
                            ->join('stockholder_accounts', 'users.id', '=', 'stockholder_accounts.userId')
                            ->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0)->whereDoesntHave('usedAmendmentAccount');
                            })
                            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                                $query->where('accountNo', $accountNo);
                            })
                            ->whereDoesntHave('stockholderAccount.proxyAmendment')
                            ->selectRaw('stockholder_accounts.accountId')
                            ->pluck('stockholder_accounts.accountId');

                        break;
                }


                Log::info("Stockholder Online Voting: Available votes for corp-rep user: " . $userInfo->id, [
                    'availableVotesBod' => $availableVotesBod,
                    'availableVotesAmendment' => $availableVotesAmendment
                ]);

                break;

            default:

                Log::error("Stockholder Online Voting: User role " . $user->role . " is not allowed to vote in Stockholder Online.", ['userId' => $userInfo->id]);
                throw new Exception("User is not allowed to vote in Stockholder Online.");


                break;
        }


        return array(
            'bod' => $availableVotesBod->toArray(),
            'amendment' => $availableVotesAmendment->toArray()
        );
    }

    public function submit($request)
    {

        Log::info('Stockholder Online Voting: Submitting ballot ID ' . $request->ballotId, [
            'ballotId' => $request->ballotId,
            'confirmationId' => $request->confirmationId
        ]);

        $this->checkIfUserIsAuthorizedVoter(Auth::user());

        $this->ensureElectionIsOngoing('submit', $request->ballotId, $request->confirmationId);


        $ballotInfo = VoteService::ballotInfo($request, 'Stockholder Online Voting');
        $confirmation = ConfirmationService::ensureBallotConfirmationIsValid($ballotInfo, $request);

        $this->ensureAvailableVotesAreUnchanged($ballotInfo, json_decode($confirmation->data, true));


        DB::beginTransaction();

        $this->voteService->updateBallotStatus($ballotInfo, $confirmation);


        $this->voteService->createBallotDetails($confirmation, $ballotInfo);
        $this->voteService->createBallotAgendaDetails($confirmation, $ballotInfo);
        $this->voteService->createBallotAmendmentDetails($confirmation, $ballotInfo);

        $this->voteService->insertUsedAccounts($confirmation);

        ActivityController::log(['activityCode' => '00094', 'ballotId' => $ballotInfo->ballotId, 'confirmationId' => $confirmation->confirmationId, 'userId' => Auth::id()]);


        DB::commit();

        VoteService::sendVoteConfirmation(Auth::user()->email, 'Stockholder Online Voting', $confirmation->id, $ballotInfo);

        return response()->json([
            'message' => 'Your vote has been successfully submitted.',
            'ballotId' => $ballotInfo->ballotId,
            'confirmationId' => $confirmation->confirmationId
        ], 200);
    }


    private static function checkIfUserIsAuthorizedVoter($userInfo): string
    {

        Log::info("Stockholder Online Voting: Checking if user is authorized voter.");

        switch ($userInfo->role) {
            case 'stockholder':
                $authorizedVoter = $userInfo->stockholder->voteInPerson;
                break;
            case 'corp-rep':
                $authorizedVoter = $userInfo->stockholderAccount->stockholder->voteInPerson;
                break;
            default:
                throw new Exception('User role is not authorized for voting.');
                break;
        }


        if (Auth::user()->role !== $authorizedVoter) {

            Log::info("Stockholder Online Voting: User not authorized to vote for Stockholder Online.", [
                'authorizedVoter' => $authorizedVoter,
                'userRole' => Auth::user()->role
            ]);

            $message = $authorizedVoter === "stockholder" ?
                "The stockholder is the authorized voter for this account. Please contact our Membership Office at 8658 4901 to 03 local 114." :
                "The corporate representative is the authorized voter for this account. Please contact our Membership Office at 8658 4901 to 03 local 114.";


            ActivityController::log(['activityCode' => '00090', 'remarks' => $message, 'userId' => Auth::user()->id]);

            throw new ValidationErrorException($message);
        }

        return $authorizedVoter;
    }

    public function store($request)
    {
        try {

            $this->ensureElectionIsOngoing('store');

            Log::info("Stockholder Online Voting: Fetching user information.");
            $userInfo = User::with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder')->findOrFail(Auth::id());

            VoteService::ensureEmailIsNotUsed($userInfo->email, 'Stockholder Online Voting');
            VoteService::validateVotingItems();

            $authorizedVoter = $this->checkIfUserIsAuthorizedVoter($userInfo);

            Log::info("Stockholder Online Voting: Fetching available votes.");
            $availableVotes =  $this->getAvailableVotes($request->revoke, $userInfo);
            Log::info("Stockholder Online Voting: Available votes fetched successfully.", ['availableVotes' => $availableVotes]);
            Log::info("Stockholder Online Voting: Fetching available accounts.");
            $availableAccounts = $this->getAvailableAccounts($userInfo);
            Log::info("Stockholder Online Voting: Available accounts fetched successfully.", ['availableAccounts' => $availableAccounts]);

            VoteService::checkIfUserCanVote($availableVotes, "Stockholder Online Voting");


            DB::beginTransaction();

            $ballotInfo = $this->saveBallot($userInfo, $availableVotes, $availableAccounts, $authorizedVoter);


            VoteService::saveAttendance($availableVotes, $ballotInfo, "Stockholder Online Voting");

            Log::info("Stockholder Online Voting: Stockholder Online Ballot created successfully.", [
                'ballotId' => $ballotInfo->ballotId,
            ]);

            ActivityController::log(['activityCode' => '00047', 'ballotId' => $ballotInfo->ballotId, 'userId' => $userInfo->id]);

            DB::commit();

            return response()->json(['ballotId' => $ballotInfo->ballotId], 200);
        } catch (ValidationErrorException $e) {

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error creating Stockholder Online Ballot");

            return response()->json([], 500);
        }
    }

    private function ensureElectionIsOngoing($action, $ballotId = null, $confirmationId = null)
    {

        if (!in_array($action, ['store', 'summary', 'submit'])) {
            Log::error("Invalid action specified for election status check: " . $action);
            throw new ValidationErrorException("Invalid action specified for election status check.");
        }

        $electionDataStatus = VoteService::isElectionOngoing("Stockholder Online Voting");

        if ($electionDataStatus !== true) {

            ActivityController::log([
                'activityCode' => '00090',
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
        $ballot = VoteService::generateBallot("Stockholder Online Voting");

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
            'authorizedVoter'           => $authorizedVoter,
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
