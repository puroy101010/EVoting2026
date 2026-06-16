<?php

namespace App\Services;


use App\Http\Controllers\ActivityController;
use App\Models\Agenda;
use App\Models\Amendment;
use App\Models\Ballot;
use App\Models\Candidate;
use App\Models\StockholderAccount;
use Exception;

use Dompdf\Dompdf;



use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class AdminBallotService
{


    public function index($request)
    {
        try {

            $candidates  =  Candidate::orderBy('lastName', 'asc')->get();
            $amendments  =  Amendment::orderBy('amendmentId', 'asc')->get();
            $agendas  =  Agenda::orderBy('agendaId', 'asc')->get();

            $ballots = Ballot::with('createdBy', 'bodDetails', 'amendmentDetails', 'agendaDetails')->get()->toArray();


            ActivityController::log(['activityCode' => '00055']);

            return view('admin.ballots', [
                'ballots' => $ballots,
                'candidates' => $candidates,
                'amendments' => $amendments,
                'agendas' => $agendas
            ]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error loading ballots');

            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }
    public function preview($request, $id)
    {
        try {


            $ballot = Ballot::with(['usedAccountBod.stockholderAccount.stockholder', 'usedAccountBod.stockholderAccount.user', 'ballotConfirmation'])->findOrFail($id);


            $usedBodAccounts = $this->generateBodAccounts($ballot->usedAccountBod);
            $usedAmendmentAccounts = $this->generateAmendmentAccounts($ballot->usedAccountAmendment);
            $availableBodAccounts = $this->generateAvailableBodAccounts($ballot->availableBodAccounts);
            $availableAmendmentAccounts = $this->generateAvailableAmendmentAccounts($ballot->availableAmendmentAccounts);



            if ($ballot->ballotConfirmation !== null) {

                $ballotCofnfirmationData = json_decode($ballot->ballotConfirmation->data, true);

                $message = $ballot->ballotConfirmation->data = $ballotCofnfirmationData['message'] ?? '';
                $usedVotes = $ballot->castedVotes ?? 0;
                $unusedVotes = $ballot->unusedVotesBod ?? 0;


                $amendmentData = $ballotCofnfirmationData['amendment'] ?? [];
                $agendaData = $ballotCofnfirmationData['agenda'] ?? [];
                $boardOfDirectorsData = $ballotCofnfirmationData['bod'] ?? [];
            }

            Log::info('Successfully previewed ballot', ['ballot_id' => $id]);


            ActivityController::log(['activityCode' => '00073', 'ballotId' => $id]);




            return view('admin.ballot_preview', [
                'votingType' => UtilityService::getVotingType($ballot->ballotType),
                "message" => $message ?? '',
                'usedVotes' => $usedVotes ?? 0,
                'unusedVotes' => $unusedVotes ?? 0,
                'ballot' => $ballot,
                'amendmentEnabled' => ConfigService::getConfig('amendment_enabled') === '1' ? true : false,
                'amendments' => $this->renderAmendmentSummary($amendmentData ?? []),
                'agendas' => $this->generateAgendaSummary($agendaData ?? []),
                'boardOfDirectors' => $this->generateBoardOfDirectorSummary($boardOfDirectorsData ?? []),
                'usedBodAccounts' => $usedBodAccounts,
                'usedAmendmentAccounts' => $usedAmendmentAccounts,
                'availableBodAccounts' => $availableBodAccounts,
                'availableAmendmentAccounts' => $availableAmendmentAccounts


            ]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error previewing ballot', ['ballot_id' => $id]);

            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    private function generateAvailableBodAccounts($availableAccounts)
    {

        $availableAccounts = json_decode($availableAccounts, true);
        if (empty($availableAccounts)) {

            return '<tr><td colspan="3" class="text-center text-muted">No available accounts for voting.</td></tr>';
        }

        $availableAccounts = StockholderAccount::whereIn('accountId', $availableAccounts)->get();

        $rows = '';
        foreach ($availableAccounts as $account) {
            $stockholderName = $account->stockholder->stockholder;
            $status = ($account->isDelinquent === 1)
                ? '<span class="badge badge-danger">Delinquent</span>'
                : '<span class="badge badge-success">Active</span>';
            $rows .= "<tr>
                        <td>{$account->accountKey}</td>
                        <td class=\"text-center\">{$stockholderName}</td>
                        <td class=\"text-center\">{$status}</td>
                    </tr>";
        }

        return $rows;
    }


    private function generateAvailableAmendmentAccounts($availableAccounts)
    {

        $availableAccounts = json_decode($availableAccounts, true);
        if (empty($availableAccounts)) {

            return '<tr><td colspan="3" class="text-center text-muted">No available accounts for voting.</td></tr>';
        }

        $availableAccounts = StockholderAccount::whereIn('accountId', $availableAccounts)->get();

        $rows = '';
        foreach ($availableAccounts as $account) {
            $stockholderName = $account->stockholder->stockholder;
            $status = ($account->isDelinquent === 1)
                ? '<span class="badge badge-danger">Delinquent</span>'
                : '<span class="badge badge-success">Active</span>';
            $rows .= "<tr>
                        <td>{$account->accountKey}</td>
                        <td class=\"text-center\">{$stockholderName}</td>
                        <td class=\"text-center\">{$status}</td>
                    </tr>";
        }

        return $rows;
    }


    private function generateBodAccounts($usedBodAccounts)
    {
        if (empty($usedBodAccounts) || count($usedBodAccounts) === 0) {
            return '<tr><td colspan="3" class="text-center text-muted">No accounts used for BOD voting.</td></tr>';
        }

        $rows = '';
        foreach ($usedBodAccounts as $usedBodAccount) {
            $stockholderAccount = $usedBodAccount->stockholderAccount ?? null;
            $stockholder = $stockholderAccount->stockholder->stockholder ?? 'N/A';
            $accountKey = $stockholderAccount->accountKey ?? 'N/A';
            $status = ($stockholderAccount && $stockholderAccount->isDelinquent === 1) ? '<span class="badge badge-danger">Delinquent</span>' : '<span class="badge badge-success">Active</span>';

            $rows .= "<tr>
                        <td>{$accountKey}</td>
                        <td class=\"text-center\">{$stockholder}</td>
                        <td class=\"text-center\">{$status}</td>
                    </tr>";
        }

        return $rows;
    }



    private function generateAmendmentAccounts($usedAmendmentAccounts)
    {
        if (empty($usedAmendmentAccounts) || count($usedAmendmentAccounts) === 0) {
            return '<tr><td colspan="3" class="text-center text-muted">No accounts used for amendment voting.</td></tr>';
        }

        $rows = '';
        foreach ($usedAmendmentAccounts as $usedAmendmentAccount) {
            $stockholderAccount = $usedAmendmentAccount->stockholderAccount ?? null;
            $stockholder = $stockholderAccount->stockholder->stockholder ?? 'N/A';
            $accountKey = $stockholderAccount->accountKey ?? 'N/A';
            $status = ($stockholderAccount && $stockholderAccount->isDelinquent === 1) ? '<span class="badge badge-danger">Delinquent</span>' : '<span class="badge badge-success">Active</span>';

            $rows .= "<tr>
                        <td>{$accountKey}</td>
                        <td class=\"text-center\">{$stockholder}</td>
                        <td class=\"text-center\">{$status}</td>
                    </tr>";
        }

        return $rows;
    }


    private function generateBoardOfDirectorSummary($data)
    {
        if (empty($data) || !is_array($data)) {
            return '<tr>
            <td colspan="2" class="text-center text-muted">No Board of Director candidates available.</td>
            </tr>';
        }

        $bods = '';
        $hasVotes = false;

        foreach ($data as $bod) {
            $name = htmlspecialchars($bod['name'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
            $type = htmlspecialchars($bod['type'] ?? '', ENT_QUOTES, 'UTF-8');
            $votes = isset($bod['vote']) ? intval($bod['vote']) : 0;

            if ($votes === 0) {
                continue;
            }

            $hasVotes = true;
            $bods .= "
            <tr class=\"td-amend\">
                <td>
                <span class=\"font-weight-bold\">{$name}</span><br>
                <small class=\"text-muted\">{$type}</small>
                </td>
                <td class=\"text-center\">{$votes}</td>
            </tr>
            ";
        }

        if (!$hasVotes) {
            $bods .= '<tr>
            <td colspan="2" class="text-center">
                <div class="no-votes-candidate-summary">
                <div class="no-votes-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="no-votes-title">No Votes Distributed</div>
                <div class="no-votes-desc">You have not assigned any votes to Board of Director candidates.</div>
                </div>
            </td>
            </tr>';
        }

        return $bods;
    }
    private function generateAgendaSummary($data): string
    {
        $counter = 1;
        $agendas = '';

        foreach ($data as $agenda) {
            $favor = $agenda["vote"]["favor"] == 1 ? 'btn-success-custom' : '';
            $notFavor = $agenda["vote"]["notFavor"] == 1 ? 'btn-success-custom' : '';
            $abstain = $agenda["vote"]["abstain"] == 1 ? 'btn-success-custom' : '';

            $agendas .= "
                <tr>
                    <td class=\"counter td-amend\">{$counter}</td>
                    <td class=\"amendment td-amend\">{$agenda['agenda']}</td>
                    <td class=\"text-center\">
                        <button type=\"button\" class=\"btn border-success-custom disabled btn-amendment btn-favor {$favor}\">
                            Favor
                        </button>
                        <button type=\"button\" class=\"btn border-success-custom disabled btn-amendment btn-not-favor {$notFavor}\">
                            Not Favor
                        </button>
                        <button type=\"button\" class=\"btn border-success-custom disabled btn-amendment btn-abstain {$abstain}\">
                            Abstain
                        </button>
                    </td>
                </tr>
            ";
            $counter++;
        }

        return $agendas;
    }


    private function renderAmendmentSummary($data): string
    {
        $amendments = '';
        $counter = 1;

        foreach ($data as $amendment) {
            $yes = $amendment['vote']['yes'] == 1 ? 'btn-success-custom' : '';
            $no = $amendment['vote']['no'] == 1 ? 'btn-success-custom' : '';

            $amendments .= "
                <tr>
                    <td class=\"counter td-amend\">{$counter}</td>
                    <td class=\"amendment td-amend\">{$amendment['amendment']}</td>
                    <td class=\"text-center\">
                        <button type=\"button\" class=\"btn border-success-custom btn-amendment disabled btn-yes {$yes}\">
                            Favor
                        </button>
                        <button type=\"button\" class=\"btn border-success-custom btn-amendment disabled btn-no {$no}\">
                            Not in favor
                        </button>
                    </td>
                </tr>
            ";
            $counter++;
        }
        return $amendments;
    }

    public function export($request)
    {


        $amendmentDetails = $this->generateAmendmentResult();
        $agendaDetails = $this->generateAgendaResult();
        $bodDetails = $this->generateBodResult();

        $amendmentDetailsPerson = $this->generateAmendmentResult('person');
        $amendmentDetailsProxy = $this->generateAmendmentResult('proxy');


        // echo '<pre>';
        // print_r($amendmentDetails);
        // echo '</pre>';

        // return;
        Log::info('Exporting election results as PDF');

        $user = Auth::user();
        $generatedBy = $user->adminAccount->firstName . ' ' . $user->adminAccount->lastName;
        $generatedAt = now()->format('Y-m-d H:i:s') . ' (' . config('app.timezone') . ')';
        $footerText = 'Generated by ' . $generatedBy . ' on ' . $generatedAt;
        $pdf = PDF::loadView('pdf.election_result', [
            'generatedBy' => $generatedBy,
            'generatedAt' => $generatedAt,
            'amendmentResults' => $amendmentDetails,
            'amendmentResultsPerson' => $amendmentDetailsPerson,
            'amendmentResultsProxy' => $amendmentDetailsProxy,
            'agendaResults' => $agendaDetails,
            'bodResults' => $bodDetails,
            'footerText' => $footerText
        ])
            ->setPaper('legal')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $pdf->setOptions(['enable_php' => true]);



        Log::info('Successfully exported election results as PDF');

        //save to server privately
        $fileName = 'Election Result_' . "ID_" . Auth::id() . '_' . now()->format('Ymd_His') . '.pdf';
        $directory = storage_path('app/private/exports/results');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $filePath = $directory . '/' . $fileName;
        $output = $pdf->output();
        file_put_contents($filePath, $output);

        Log::info('Election results PDF saved to server', ['file_path' => $filePath]);

        ActivityController::log(['activityCode' => '00058']);

        return $pdf->stream('election_result.pdf');
    }


    private function generateAmendmentResult($votingType = 'all')
    {


        if (!in_array($votingType, ['all', 'person', 'proxy'])) {

            throw new Exception('Invalid voting type specified.');
        }

        // $amendmentVotes = Amendment::where('isActive', true)
        //     ->with('ballotAmendmentDetails')
        //     ->get();



        $amendmentVotes = Amendment::where('isActive', true)
            ->with('ballotAmendmentDetails.ballot', function ($query) use ($votingType) {
                if ($votingType !== 'all') {
                    $query->where('ballotType', $votingType);
                }
            })
            ->get();



        $results = [];


        foreach ($amendmentVotes as $vote) {
            $yes = $vote->ballotAmendmentDetails->sum(function ($detail) {
                $amendmentVote = ($detail->ballot->availableVotesAmendment ?? 0);
                return $amendmentVote * ($detail->yes ?? 0);
            });

            $no = $vote->ballotAmendmentDetails->sum(function ($detail) {
                $amendmentVote = ($detail->ballot->availableVotesAmendment ?? 0);
                return $amendmentVote * ($detail->no ?? 0);
            });


            $results[] = [
                'amendment_id' => $vote->amendmentId,
                'amendment' => $vote->amendmentDesc,
                'yes' => $yes,
                'no' => $no,
            ];
        }

        return $results;
    }

    private function generateAgendaResult()
    {
        $agendaVotes = Agenda::where('isActive', true)
            ->with('ballotAgendaDetails')
            ->get();

        $results = [];

        $votePerShare = ConfigService::getConfig('votes_per_share');

        foreach ($agendaVotes as $vote) {
            $favorCount = $vote->ballotAgendaDetails->sum(function ($detail) use ($votePerShare) {
                $agendaVote = ($detail->ballot->availableVotesBod ?? 0) / $votePerShare;
                return $agendaVote * ($detail->favor ?? 0);
            });

            $notFavorCount = $vote->ballotAgendaDetails->sum(function ($detail) use ($votePerShare) {
                $agendaVote = ($detail->ballot->availableVotesBod ?? 0) / $votePerShare;
                return $agendaVote * ($detail->notFavor ?? 0);
            });
            $abstainCount = $vote->ballotAgendaDetails->sum(function ($detail) use ($votePerShare) {
                $agendaVote = ($detail->ballot->availableVotesBod ?? 0) / $votePerShare;
                return $agendaVote * ($detail->abstain ?? 0);
            });

            $results[] = [
                'agenda_id' => $vote->agendaId,
                'agenda' => $vote->agendaDesc,
                'favor' => $favorCount,
                'not_favor' => $notFavorCount,
                'abstain' => $abstainCount,
            ];
        }

        return $results;
    }

    private function generateBodResult()
    {
        $bodVotes = Candidate::where('isActive', true)
            ->with('ballotBodDetails')
            ->get();

        $results = [];

        foreach ($bodVotes as $vote) {
            $totalVotes = $vote->ballotBodDetails->sum('vote');

            $stockholderOnline = $vote->ballotBodDetails()
                ->whereHas('ballot', function ($query) {
                    $query->where('ballotType', 'person');
                })
                ->sum('vote');


            $proxyVoting = $vote->ballotBodDetails()
                ->whereHas('ballot', function ($query) {
                    $query->where('ballotType', 'proxy');
                })
                ->sum('vote');

            $results[] = [
                'candidate_id' => $vote->candidateId,
                'candidate' => $vote->firstName . ' ' . $vote->lastName,
                'type' => $vote->type,
                'stockholder_online' => $stockholderOnline,
                'proxy_voting' => $proxyVoting,
                'total_votes' => $totalVotes,
            ];
        }


        usort($results, function ($a, $b) {
            return $b['total_votes'] <=> $a['total_votes'];
        });

        return $results;
    }
}
