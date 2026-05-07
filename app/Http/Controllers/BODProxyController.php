<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreBODProxyRequest;

use App\Models\ProxyBoardOfDirector;
use App\Services\BODProxyService;
use App\Models\StockholderAccount;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BODExport;
use App\Http\Requests\CancelProxyBoardOfDirectorRequest;

use App\Http\Requests\ExportActiveBoardOfDirectorProxiesRequest;
use App\Http\Requests\ExportMasterlistBoardOfDirectorProxiesRequest;
use App\Http\Requests\PrintProxyByAssigneeRequest;
use App\Http\Requests\ShowBodProxyRequest;
use App\Services\ProxyService;
use App\Services\UtilityService;

class BODProxyController extends Controller
{


    protected $bodProxyService;

    public function __construct(BODProxyService $bodProxyService)
    {
        $this->bodProxyService = $bodProxyService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->bodProxyService->index($request);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBODProxyRequest $request)
    {

        return $this->bodProxyService->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ShowBodProxyRequest $request, $id)
    {
        try {

            Log::info("Fetching BOD proxyholder details for Account ID: $id");
            $stockholderAccount = StockholderAccount::with([
                'stockholder',
                'stockholder.stockholderAccounts',
                'proxyBoard',
                'proxyBoard.assignee',
                'proxyBoard.assignee.stockholder',
                'proxyBoard.assignee.stockholderAccount',
                'proxyBoard.assignee.stockholderAccount.stockholder',
                'proxyBoard.assignee.nonMemberAccount',
                'proxyBoard.assignor',
                'proxyBoard.assignor.stockholder',
                'proxyBoard.assignor.stockholderAccount',
                'proxyBoard.assignor.stockholderAccount.stockholder',

            ])->find($id);

            Log::info("Fetched BOD proxyholder details for Account ID: $id");

            return $stockholderAccount;
        } catch (Exception $e) {

            Log::error("Error fetching proxyholder details for Account ID: $id", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e

            ]);

            return response()->json([], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function cancel(CancelProxyBoardOfDirectorRequest $request, $id)
    {

        return $this->bodProxyService->cancel($request, $id);
    }

    public function audit(Request $request, $id)
    {
        return $this->bodProxyService->audit($request, $id);
    }


    public function summary(Request $request)
    {
        try {


            if (!Auth::user()->can('view bod proxy summary')) {
                Log::warning("BOD Proxy: Unauthorized access attempt to view Board of Director proxy summary");
                return view('errors.response', [
                    'code' => 403,
                    'message' => 'You do not have permission to view Board of Director proxy summary.'
                ]);
            }

            Log::info("BOD Proxy: Accessed Board of Director proxy summary");

            return view('admin.proxy_bod_summary', [
                'proxyholders' => $this->bodProxyService->getSummary()
            ]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while fetching BOD proxy summary');
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    public function proxy_list(Request $request, $id)
    {
        try {

            if (!Auth::user()->can('view bod proxy assignor')) {
                Log::warning("BOD Proxy: Unauthorized access attempt to view BOD proxy assignor");
                return view('errors.response', [
                    'code' => 403,
                    'message' => 'You do not have permission to view BOD proxy assignor.'
                ]);
            }

            Log::info("BOD Proxy: Accessed BOD proxy assignor");

            ActivityController::log([
                'activityCode' => '00139'
            ]);


            return response()->json(['proxyList' => $this->bodProxyService->getProxyList($request, $id)]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while fetching BOD proxy list');

            return response()->json([], 500);
        }
    }

    public function masterlist(Request $request)
    {

        if (!Auth::user()->can('view bod proxy masterlist')) {
            Log::warning("BOD Proxy: Unauthorized access attempt to view Board of Director proxy masterlist");
            return view('errors.response', [
                'code' => 403,
                'message' => 'You do not have permission to view Board of Director proxy masterlist.'
            ]);
        }

        Log::info("BOD Proxy: Accessed Board of Director proxy masterlist");

        return view('admin.proxy_bod_masterlist', [
            'proxies' => $this->bodProxyService->masterlist($request),
            'filter' => $request->filter ?? 'all'
        ]);
    }

    public function history(Request $request, $id)
    {
        try {

            if (!Auth::user()->can('view bod proxy history')) {
                Log::warning("BOD Proxy: Unauthorized access attempt to view Board of Director proxy history");
                return response()->json([
                    'error' => 'Unauthorized access'
                ], 403);
            }

            $history = ProxyService::processHistory($request, $id, 'BOD');

            Log::info("BOD Proxy: Accessed Board of Director proxy history");

            return response()->json(['history' => $history]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while fetching BOD proxy history');
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function exportMasterlist(ExportMasterlistBoardOfDirectorProxiesRequest $request)
    {

        return $this->bodProxyService->exportMasterlist($request);
    }

    public function exportActiveProxies(ExportActiveBoardOfDirectorProxiesRequest $request)
    {
        return $this->bodProxyService->exportActiveProxies($request);
    }

    public function printProxyByAssignee(PrintProxyByAssigneeRequest $request)
    {

        return $this->bodProxyService->printProxyByAssignee($request, $request->id);
    }
}
