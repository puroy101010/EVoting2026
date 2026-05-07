<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreAmendmentProxyRequest;

use App\Services\AmendmentProxyService;
use App\Models\StockholderAccount;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Requests\CancelProxyAmendmentRequest;
use App\Http\Requests\ExportActiveAmendmentProxiesRequest;
use App\Http\Requests\ExportMasterlistAmendmentProxiesRequest;
use App\Http\Requests\ShowAmendmentProxyRequest;
use App\Models\ProxyAmendment;
use App\Services\ProxyService;
use App\Services\UtilityService;


class AmendmentProxyController extends Controller
{


    protected $amendmentProxyService;

    public function __construct(AmendmentProxyService $amendmentProxyService)
    {
        $this->amendmentProxyService = $amendmentProxyService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->amendmentProxyService->index($request);
    }


    public function exportActiveProxies(ExportActiveAmendmentProxiesRequest $request)
    {
        return $this->amendmentProxyService->exportActiveProxies($request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAmendmentProxyRequest $request)
    {

        return $this->amendmentProxyService->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ShowAmendmentProxyRequest $request, $id)
    {
        try {

            Log::info("Fetching Amendment proxyholder details for Account ID: $id");
            $stockholderAccount = StockholderAccount::with([
                'stockholder',
                'stockholder.stockholderAccounts',
                'proxyAmendment',
                'proxyAmendment.assignee',
                'proxyAmendment.assignee.stockholder',
                'proxyAmendment.assignee.stockholderAccount',
                'proxyAmendment.assignee.stockholderAccount.stockholder',
                'proxyAmendment.assignee.nonMemberAccount',
                'proxyAmendment.assignor',
                'proxyAmendment.assignor.stockholder',
                'proxyAmendment.assignor.stockholderAccount',
                'proxyAmendment.assignor.stockholderAccount.stockholder',

            ])->find($id);

            Log::info("Fetched Amendment proxyholder details for Account ID: $id");

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


    public function cancel(CancelProxyAmendmentRequest $request, $id)
    {

        return $this->amendmentProxyService->cancel($request, $id);
    }

    public function audit(Request $request, $id)
    {
        return $this->amendmentProxyService->audit($request, $id);
    }



    public function summary(Request $request)
    {
        try {

            if (!Auth::user()->can('view amendment proxy summary')) {
                Log::warning("Amendment Proxy: Unauthorized access attempt to view Amendment proxy summary");
                return view('errors.response', [
                    'code' => 403,
                    'message' => 'You do not have permission to view Amendment proxy summary.'
                ]);
            }

            Log::info("Amendment Proxy: Accessed Amendment proxy summary");
            return view('admin.proxy_amendment_summary', [
                'proxyholders' => $this->amendmentProxyService->getSummary()
            ]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while fetching Amendment proxy summary');
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    public function proxy_list(Request $request, $id)
    {
        try {

            if (!Auth::user()->can('view amendment proxy assignor')) {
                Log::warning("Amendment Proxy: Unauthorized access attempt to view Amendment proxy assignor");
                return view('errors.response', [
                    'code' => 403,
                    'message' => 'You do not have permission to view Amendment proxy assignor.'
                ]);
            }

            Log::info("Amendment Proxy: Accessed Amendment proxy assignor");

            return response()->json(['proxyList' => $this->amendmentProxyService->getProxyList($request, $id)]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while fetching Amendment proxy list');

            return response()->json([], 500);
        }
    }

    public function masterlist(Request $request)
    {


        if (!Auth::user()->can('view amendment proxy masterlist')) {
            Log::warning("Amendment Proxy: Unauthorized access attempt to view Amendment proxy masterlist");
            return view('errors.response', [
                'code' => 403,
                'message' => 'You do not have permission to view Amendment proxy masterlist.'
            ]);
        }

        Log::info("Amendment Proxy: Accessed Amendment proxy masterlist");
        return view('admin.proxy_amendment_masterlist', [
            'proxies' => $this->amendmentProxyService->masterlist($request),
            'filter' => $request->filter ?? 'all'
        ]);
    }


    public function exportMasterlist(ExportMasterlistAmendmentProxiesRequest $request)
    {

        return $this->amendmentProxyService->exportMasterlist($request);
    }

    public function history(Request $request, $id)
    {
        try {

            if (!Auth::user()->can('view amendment proxy history')) {
                Log::warning("Amendment Proxy: Unauthorized access attempt to view Amendment proxy history");
                return response()->json([
                    'error' => 'Unauthorized access'
                ], 403);
            }

            $history = ProxyService::processHistory($request, $id, 'Amendment');

            Log::info("Amendment Proxy: Accessed Amendment proxy history");

            return response()->json(['history' => $history]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Error occurred while fetching Amendment proxy history');
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
