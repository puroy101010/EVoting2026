<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailableVoteInquiryIndexRequest;
use App\Http\Requests\ShowAvailableVoteInquiryRequest;
use App\Models\ProxyAmendment;
use App\Models\ProxyBoardOfDirector;
use App\Models\User;
use App\Services\ConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AvailableVoteInquiryController extends Controller
{
    public function index(AvailableVoteInquiryIndexRequest $request)
    {
        Log::info("Accessing Available Vote Inquiry Page");

        $users = User::with(['stockholder', 'stockholderAccount', 'nonMemberAccount'])->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])->get();

        $usersArray = [];

        foreach ($users as $user) {

            $name = '';
            $accountNo = '';
            $isCorpRepIndividual = false;

            switch ($user->role) {
                case 'stockholder':
                    $name = $user->stockholder->stockholder;
                    $accountNo = $user->stockholder->accountNo;
                    break;
                case 'corp-rep':
                    if ($user->stockholderAccount->stockholder->accountType === 'indv') {
                        $isCorpRepIndividual = true;
                    }

                    $name = $user->stockholderAccount->corpRep ?? 'corp-rep |' . $user->stockholderAccount->stockholder->stockholder;


                    $accountNo = $user->stockholderAccount->accountKey;
                    break;

                case 'non-member':
                    $name = $user->nonMemberAccount->lastName . ', ' . $user->nonMemberAccount->firstName;
                    $accountNo = $user->nonMemberAccount->nonmemberAccountNo;
                    break;
                default:
                    throw new \Exception('Invalid role: ' . $user->role);
            }


            if (!$isCorpRepIndividual) {

                $usersArray[] = [
                    'id' => $user->id,
                    'name' => $name ?? 'no corp rep',
                    'email' => $user->email,
                    'accountNo' => $accountNo,
                    'role' => $this->formatRole($user->role)

                ];
            }
        }


        ActivityController::log([
            'activityCode' => '00135'
        ]);

        Log::info("Available Vote Inquiry Page accessed successfully.");

        return view('admin.available_vote_inquiry', [
            'users' => $usersArray
        ]);
    }

    private function formatRole($role): string
    {
        switch ($role) {
            case 'stockholder':
                return 'Stockholder';
            case 'corp-rep':
                return 'Corporate Representative';
            case 'non-member':
                return 'Non-Member';
            default:
                throw new \Exception('Invalid role: ' . $role);
        }
    }
    public function search(Request $request) {}
    public function show(ShowAvailableVoteInquiryRequest $request, int $id)
    {

        Log::info("Fetching available votes in the inquiry page for user ID: $id");

        $userInfo = User::with(['stockholder', 'stockholderAccount', 'stockholderAccount.stockholder'])
            ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
            ->findOrFail($id);



        $availableVotes = $this->getUserProxies($userInfo);

        $bodProxyCount = collect($availableVotes['bod'])->count();
        $bodRevokedCount = collect($availableVotes['bod'])->whereNotNull('used_account')->count();
        $bodDelinquentCount = collect($availableVotes['bod'])->filter(function ($item) {
            return $item['stockholder_account']['isDelinquent'] === 1;
        })->count();




        $votePerShare = ConfigService::getConfig('votes_per_share');

        $netAvailableProxy = $bodProxyCount - $bodDelinquentCount - $bodRevokedCount;


        ActivityController::log([
            'activityCode' => '00136',
            'userId' => $userInfo->id,
            'remarks' => "Viewed available votes in the inquiry page for $userInfo->full_name | Account No: " . $userInfo->account_no
        ]);


        Log::info("Fetched available votes in the inquiry page for user ID: $id", [
            'accountNo' => $userInfo->account_no,
            'role' => $this->formatRole($userInfo->role),
            'name' => $userInfo->full_name,
            'email' => $userInfo->email,
            'validProxy' => $bodProxyCount,
            'delinquentProxy' => $bodDelinquentCount,
            'revokedProxy' => $bodRevokedCount,
            'netAvailableProxy' => $netAvailableProxy,
            'availableVote' => $netAvailableProxy * $votePerShare
        ]);

        return response()->json([
            "accountNo" => $userInfo->account_no,
            "role" => $this->formatRole($userInfo->role),
            "name" => $userInfo->full_name,
            "email" => $userInfo->email,
            "validProxy" => $bodProxyCount,
            "delinquentProxy" => $bodDelinquentCount,
            "revokedProxy" => $bodRevokedCount,
            "netAvailableProxy" => $netAvailableProxy,
            "availableVote" => $netAvailableProxy * $votePerShare

        ]);
    }


    private function getUserProxies($userInfo)
    {




        switch ($userInfo->role) {

            case 'stockholder':

                Log::info("Vote Inquiry: Fetching available votes for stockholder.");

                $availableVotesBod = ProxyBoardOfDirector::with(['usedAccount', 'stockholderAccount'])->where('assigneeId', $userInfo->id)
                    ->get();

                $availableVotesAmendment = [];

                break;

            case 'corp-rep':

                Log::info("Vote Inquiry: Fetching available votes for corp-rep.");
                Log::info("Vote Inquiry: Fetching all stockholder accounts belonging to corp-rep by email " . Auth::user()->email . " and account no " . Auth::user()->stockholderAccount->stockholder->accountNo);

                $corpRepAccounts = User::leftJoin('stockholder_accounts', 'stockholder_accounts.userId', '=', 'users.id')
                    ->leftJoin('stockholders', 'stockholders.stockholderId', '=', 'stockholder_accounts.stockholderId')
                    ->selectRaw('stockholder_accounts.accountId, users.id')
                    ->where('users.email', Auth::user()->email)
                    ->where('stockholders.accountNo', Auth::user()->stockholderAccount->stockholder->accountNo)
                    ->get();

                Log::info("Vote Inquiry: Found " . count($corpRepAccounts) . " stockholder accounts belonging for corp-rep.", [
                    'corpRepEmail' => Auth::user()->email,
                    'accountNo' => Auth::user()->stockholderAccount->stockholder->accountNo,
                    'stockholderAccounts' => $corpRepAccounts->pluck('accountId')->toArray()
                ]);


                $assigneeAccountIds = $corpRepAccounts->pluck('id')->toArray();



                Log::info("Vote Inquiry: Found " . count($assigneeAccountIds) . " stockholder accounts (User ID) for corp-rep.", [
                    'stockholderAccountUserIds' => $assigneeAccountIds
                ]);


                $availableVotesBod = ProxyBoardOfDirector::with(['usedAccount', 'stockholderAccount'])->whereIn('assigneeId', $assigneeAccountIds)
                    ->get();


                $availableVotesAmendment = [];

                break;

            case 'non-member':

                Log::info("Vote Inquiry: Fetching available votes for non-member.");

                $availableVotesBod = ProxyBoardOfDirector::with(['usedAccount', 'stockholderAccount'])->where('assigneeId', $userInfo->id)
                    ->get();


                $availableVotesAmendment = [];


                if ($userInfo->nonMemberAccount->isGM === 1) {
                    $availableVotesAmendment = ProxyAmendment::with(['usedAccount', 'stockholderAccount'])->where('assigneeId', $userInfo->id)
                        ->get();
                }

                break;
        }


        $bodProxy = $availableVotesBod->toArray();
        $amendmentProxy = is_array($availableVotesAmendment) ? [] : $availableVotesAmendment->toArray();





        return array(
            'bod' => $bodProxy,
            'amendment' => $amendmentProxy
        );
    }
}
