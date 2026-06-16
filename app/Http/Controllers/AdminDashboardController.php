<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stockholder;
use App\Models\StockholderAccount;
use App\Models\Candidate;
use App\Models\ProxyAmendment;
use App\Models\ProxyBoardOfDirector;

use Exception;
use App\Models\Ballot;
use App\Models\UsedAmendmentAccount;
use App\Models\UsedBoardOfDirectorAccount;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {


            $recentUploads     = [];



            $unusedVoteWithoutBodProxy = StockholderAccount::whereDoesntHave('usedBod')->whereDoesntHave('proxyBoard')->count();
            $unusedVoteWithoutAmendmentProxy = StockholderAccount::whereDoesntHave('usedAmendment')->whereDoesntHave('proxyAmendment')->count();



            $revokedBodProxies = ProxyBoardOfDirector::whereHas('usedAccount.ballot', function ($query) {
                $query->where('ballotType', 'person');
            })->count();


            $revokedAmendmentProxies = ProxyAmendment::whereHas('usedAccount.ballot', function ($query) {
                $query->where('ballotType', 'person');
            })->count();

            $amendmentProxyController = new \App\Services\AmendmentProxyService();
            $amendmentQuorumCount = $amendmentProxyController->countQuorumProxies();

            $bodProxyController = new \App\Services\BODProxyService();
            $bodQuorumCount = $bodProxyController->countQuorumProxies();


            $attendanceController = new AttendanceController();
            $attendance = $attendanceController->getAttendanceData() ?? collect();


            $attendanceCountStockholderOnline = $attendance->filter(function ($item) {
                return optional($item->ballot)->ballotType === 'person';
            })->count();

            $attendanceCountProxy = $attendance->filter(function ($item) {
                return optional($item->ballot)->ballotType === 'proxy';
            })->count();


            return view(
                'admin.dashboard',

                [
                    "stockholders"      => Stockholder::count(),
                    "totalStocks"       => StockholderAccount::count(),
                    "activeStocks"      => StockholderAccount::where('isDelinquent', 0)->count(),
                    "delinquentStocks"  => StockholderAccount::where('isDelinquent', 1)->count(),
                    "candidate"         => Candidate::count(),
                    "recent_uploads"    => [],
                    "proxyBod"          => ProxyBoardOfDirector::count(),
                    "proxyAmendment"    => ProxyAmendment::count(),
                    'stockholderBallot' => Ballot::where('isSubmitted', true)->where('ballotType', 'person')->count(),
                    'proxyBallot'       => Ballot::where('isSubmitted', true)->where('ballotType', 'proxy')->count(),
                    'usedBod'           => UsedBoardOfDirectorAccount::count(),
                    'usedAmendment'     => UsedAmendmentAccount::count(),
                    'revokedBod'        => $revokedBodProxies,
                    'revokedAmendment'  => $revokedAmendmentProxies,
                    'unusedVotesOnline' => Ballot::where('ballotType', 'person')->where('isSubmitted', true)->sum('unusedVotesBod'),
                    'unusedVotesProxy' => Ballot::where('ballotType', 'proxy')->where('isSubmitted', true)->sum('unusedVotesBod'),

                    'unusedVotesOnlineList' =>  Ballot::where('ballotType', 'person')->where('isSubmitted', true)->where('unusedVotesBod', '>', 0)->selectRaw('ballotId, ballotNo, unusedVotesBod')->get(),
                    'unusedVotesProxyList'  =>  Ballot::where('ballotType', 'proxy')->where('isSubmitted', true)->where('unusedVotesBod', '>', 0)->selectRaw('ballotId, ballotNo, unusedVotesBod')->get(),

                    'unusedVoteWithoutBodProxy' => $unusedVoteWithoutBodProxy,
                    'unusedVoteWithoutAmendmentProxy' => $unusedVoteWithoutAmendmentProxy,

                    'amendmentQuorumCount' => $amendmentQuorumCount,
                    'bodQuorumCount' => $bodQuorumCount,

                    'stockholderOnlineAttendance' => $attendanceCountStockholderOnline,
                    'proxyVotingAttendance' => $attendanceCountProxy,


                ]
            );
        } catch (Exception $e) {

            Log::critical($e);
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }
}
