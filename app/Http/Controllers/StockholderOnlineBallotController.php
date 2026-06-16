<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationErrorException;
use App\Http\Requests\StoreStockholderOnlineBallotRequest;
use App\Http\Requests\SubmitStockholderOnlineRequest;
use App\Http\Requests\SummaryStockholderOnlineRequest;
use App\Models\Agenda;
use App\Models\Amendment;
use App\Models\Ballot;
use App\Models\Candidate;
use App\Models\Configuration;
use App\Models\User;
use App\Services\UtilityService;
use App\Services\ConfigService;
use App\Services\VoteService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\StockholderOnlineBallotService;

class StockholderOnlineBallotController extends Controller
{

    private $stockholderOnlineBallotService;

    public function __construct(StockholderOnlineBallotService $stockholderOnlineBallotService)
    {
        $this->stockholderOnlineBallotService = $stockholderOnlineBallotService;
    }

    public function store(StoreStockholderOnlineBallotRequest $request)
    {

        return $this->stockholderOnlineBallotService->store($request);
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

                return redirect('user/vote');
            }



            Log::info("Stockholder Online Voting: Loading user information.", [
                'ballotId' => $id,
            ]);

            $userInfo = User::with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder')->findOrFail(Auth::id());


            $this->markBallotAsViewed($ballotInfo);


            Log::info("Stockholder Online Voting: Successfully displayed ballot information.", [
                'ballotId' => $id,
            ]);

            $amendment = Amendment::where('isActive', 1)->get();
            $agenda = Agenda::where('isActive', 1)->get();

            return view('user.stockholder-onlilne-form', [

                'ballotId' => $ballotInfo->ballotId,
                'ballotNo' => $ballotInfo->ballotNo,
                'userInfo' => $userInfo,
                'availableVotesBod' => $ballotInfo->availableVotesBod,
                'availableVotesAmendment' => $ballotInfo->availableVotesAmendment,
                'availableSharesBod' => count(json_decode($ballotInfo->availableBodAccounts)),
                'availableSharesAmendment' => count(json_decode($ballotInfo->availableAmendmentAccounts)),
                'candidates'    => Candidate::where('isActive', 1)->orderBy('type', 'asc')->orderBy('lastName', 'asc')->orderBy('type', 'asc')->get(),
                'amendmentForm'    => VoteService::generateAmendmentForm($amendment, $ballotInfo->availableVotesAmendment),
                'agendaForm'    => VoteService::generateAgendaForm($agenda, $ballotInfo->availableVotesBod),
                'configuration'  => Configuration::all()->toArray(),
                'amendmentEnabled'  => ConfigService::getConfig('amendment_enabled') === '1'

            ]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error displaying Stockholder Online Ballot for ID {$id}.");

            return view('errors.500');
        }
    }




    private function markBallotAsViewed($ballotInfo): Ballot
    {

        $ballotInfo->isViewed = true;
        $ballotInfo->save();

        Log::info("Stockholder Online Voting: Ballot ID {$ballotInfo->ballotId} marked as viewed.", [
            'ballotId' => $ballotInfo->ballotId,
        ]);

        return $ballotInfo;
    }



    public function submit(SubmitStockholderOnlineRequest $request)
    {
        return $this->stockholderOnlineBallotService->submit($request);
    }

    public function summary(SummaryStockholderOnlineRequest $request)
    {

        return $this->stockholderOnlineBallotService->summary($request);
    }
}
