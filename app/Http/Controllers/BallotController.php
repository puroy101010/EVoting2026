<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Amendment;
use App\Models\Ballot;
use App\Models\Candidate;

use Exception;

use Illuminate\Support\Facades\Log;


use Dompdf\Dompdf;
use App\Models\StockholderAccount;
use App\Http\Requests\ExportBallotRequest;
use App\Models\ProxyAmendment;
use App\Models\ProxyBoardOfDirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\BallotService;

class BallotController extends Controller
{


    public function create(Request $request)
    {
        return (new BallotService())->create($request);
    }



    public function export(ExportBallotRequest $request)
    {

        try {

            $candidates = Candidate::with('votesBod.ballot')->leftJoin('ballot_details', 'ballot_details.candidateId', '=', 'candidates.candidateId')
                ->leftJoin('ballots', 'ballots.ballotId', 'ballot_details.ballotId')
                ->select('candidates.candidateId', DB::raw('CONCAT(firstName, " ", lastName) AS candidateName'), 'ballots.ballotType', 'candidates.type', DB::raw('SUM(vote) as totalVotes'))
                ->groupBy('candidates.type', 'candidates.candidateId')
                ->orderByDesc('totalVotes')
                ->get();



            $bodSummary = [];

            foreach ($candidates as $candidate) {

                $onlineCount = 0;
                $proxyCount = 0;

                if ($candidate->votesBod !== null) {

                    foreach ($candidate->votesBod as $bodVote) {

                        if ($bodVote->ballot->ballotType === 'person') {
                            $onlineCount = $onlineCount + $bodVote->vote;
                        }

                        if ($bodVote->ballot->ballotType === 'proxy') {
                            $proxyCount = $proxyCount + $bodVote->vote;
                        }
                    }
                }

                $bodSummary[] = array(
                    'candidateName' => $candidate->candidateName,
                    'online' => $onlineCount,
                    'proxy' => $proxyCount,
                    'total' => $candidate->totalVotes,
                    'candidateType' => $candidate->type,

                );
            }





            $amendmentData = Amendment::leftJoin('ballot_amendments', 'ballot_amendments.amendmentId', '=', 'amendments.amendmentId')
                ->leftJoin('ballots', 'ballots.ballotId', '=', 'ballot_amendments.ballotId')
                ->selectRaw('amendments.amendmentDesc, SUM(ballot_amendments.yes * ballots.availableVotesAmendment) as totalYes, SUM(ballot_amendments.no * ballots.availableVotesAmendment) as totalNo')
                ->groupBy('amendments.amendmentId')
                ->get();

            $agendaData = Agenda::leftJoin('ballot_agendas', 'ballot_agendas.agendaId', '=', 'agendas.agendaId')
                ->leftJoin('ballots', 'ballots.ballotId', '=', 'ballot_agendas.ballotId')
                ->selectRaw('agendas.agendaDesc, SUM(ballot_agendas.favor * (ballots.availableVotesBod/9)) as totalFavor, SUM(ballot_agendas.notFavor * (ballots.availableVotesBod/9)) as totalNotFavor, SUM(ballot_agendas.abstain * (ballots.availableVotesBod/9)) as totalAbstain')
                ->groupBy('agendas.agendaId')
                ->get();








            $dompdf = new Dompdf();
            $html = view('pdf.tally', ['bodSummary' => $bodSummary, 'amendmentData' => $amendmentData, 'agendaData' => $agendaData]); // Replace 'pdf.template' with the actual view name
            $dompdf->loadHtml($html);

            $dompdf->setPaper('A4', 'portrait');

            $dompdf->render();


            ActivityController::log(['activityCode' => '00058']);

            return $dompdf->stream('document.pdf', array("Attachment" => false));
        } catch (Exception $e) {

            Log::critical($e);

            return view('errors.response', ['code' => 500, 'message' => EApp::SERVER_ERROR]);
        }
    }


    public function masterlist()
    {

        try {




            $accounts = StockholderAccount::with([


                'stockholder' => function ($query) {
                    $query->select('stockholderId', 'accountNo', 'stockholder');
                },
                'usedBod.ballot' => function ($query) {
                    $query->select('ballotId', 'ballotNo', 'ballotType', 'revoked');
                },

                'usedBod' => function ($query) {
                    $query->select('accountId', 'ballotId');
                },

                'usedAmendment' => function ($query) {
                    $query->select('accountId', 'ballotId');
                },

                'usedAmendment.ballot' => function ($query) {
                    $query->select('ballotId', 'ballotNo', 'ballotType', 'revoked');
                }
            ])

                ->orderBy('accountKey')


                ->get();



            $delinquentAccountsWithBODProxies = StockholderAccount::with('stockholder')->whereHas('proxyBoard')->where('isDelinquent', true)->orderBy('accountKey')->get();
            $delinquentAccountsWithAmendmentProxies = StockholderAccount::with('stockholder')->whereHas('proxyAmendment')->where('isDelinquent', true)->orderBy('accountKey')->get();




            $revokedBod = StockholderAccount::with('stockholder')
                ->leftJoin('used_bod_accounts', 'used_bod_accounts.accountId', '=', 'stockholder_accounts.accountId')
                ->leftJoin('ballots', 'ballots.ballotId', '=', 'used_bod_accounts.ballotId')
                ->where('ballots.ballotType', 'person')
                ->whereHas('proxyBoard')->whereHas('usedBod')->count();

            $revokedAmendment = StockholderAccount::with('stockholder')
                ->leftJoin('used_amendment_accounts', 'used_amendment_accounts.accountId', '=', 'stockholder_accounts.accountId')
                ->leftJoin('ballots', 'ballots.ballotId', '=', 'used_amendment_accounts.ballotId')
                ->where('ballots.ballotType', 'person')
                ->whereHas('proxyAmendment')->whereHas('usedAmendment')->count();
            // echo '<pre>';

            //     print_r($delinquentAccountsWithBODProxies->toArray());

            // echo '</pre>';


            // return;

            $dompdf = new Dompdf();
            $html = view('pdf.masterlist', [
                'accounts' => $accounts,
                'delinquentAccountsWithBODProxies' => $delinquentAccountsWithBODProxies,
                'delinquentAccountsWithAmendmentProxies' => $delinquentAccountsWithAmendmentProxies,
                'bodProxyCount' => ProxyBoardOfDirector::count(),
                'amendmentProxyCount' => ProxyAmendment::count(),
                'revokedBod' => $revokedBod,
                'revokedAmendment' => $revokedAmendment
            ]); // Replace 'pdf.template' with the actual view name
            $dompdf->loadHtml($html);

            $dompdf->setPaper('A4', 'landscape');

            $dompdf->render();


            // ActivityController::log(['activityCode' => '00058']);

            return $dompdf->stream('Masterlist.pdf', array("Attachment" => false));
        } catch (Exception $e) {

            Log::critical($e);

            return view('errors.response', ['code' => 500, 'message' => EApp::SERVER_ERROR]);
        }
    }
}
