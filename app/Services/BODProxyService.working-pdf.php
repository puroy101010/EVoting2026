<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EApp;
use App\Http\Requests\ExportActiveBoardOfDirectorProxiesRequest;
use App\Models\NonMemberAccount;
use App\Models\Stockholder;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;




use App\Models\ProxyBoardOfDirector;
use App\Models\ProxyBoardOfDirectorCancelled;
use App\Models\ProxyBoardOfDirectorHistory;
use App\Models\StockholderAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class BODProxyService
{
    public function index($request)
    {
        try {

            if (!Auth::user()->can('view active bod proxy')) {
                Log::warning("BOD Proxy: Unauthorized access attempt to view active BOD proxies");
                return view('errors.response', [
                    'code' => 403,
                    'message' => 'You do not have permission to view active Board of Director proxies.'
                ]);
            }

            $filter =  $this->validateIndexFilter($request);

            Log::info("BOD Proxy: Active BOD Proxy accessed");


            ActivityController::log(['activityCode' => '00111']);



            return view('admin.proxy_bods', [
                'proxyholders' => $this->getProxies($request, $filter),
                'filter' => $filter

            ]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Error fetching BOD proxyholders");
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    public function exportActiveProxies($request)
    {
        $filter =  $this->validateIndexFilter($request);

        $activeProxies =  $this->getProxies($request, $filter);

        $filter = $request->filter === null ? 'all' : strtolower($request->filter);

        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        Log::info("Board of Directors Proxy: Exporting active proxies", [
            "title" => 'Board of Directors Active Proxies (' . $filter . ') ' . $currentDateTime . '.xlsx'
        ]);
        ActivityController::log(['activityCode' => '00129']);
        Log::info("Board of Directors Proxy: Exported active proxies", [
            "title" => 'Board of Directors Active Proxies (' . $filter . ') ' . $currentDateTime . '.xlsx'
        ]);

        return Excel::download(new \App\Exports\BoardOfDirectorProxyActiveExport($activeProxies), 'Board of Directors Active Proxies ' . $currentDateTime . ' (' . $filter . ') ' . '.xlsx');
    }

    private function validateIndexFilter($request)
    {

        if (!in_array($request->filter, [null, 'all', 'verified', 'unverified'])) {
            Log::error("Invalid filter value: " . $request->filter);
            throw new ValidationErrorException("Invalid filter value.");
        }

        return $request->filter;
    }

    public function store($request)
    {
        try {

            DB::beginTransaction();

            Log::info('Storing BOD Proxy', [
                'request' => $request->all()
            ]);


            $this->validateAssignor($request);
            $this->validateAssignee($request);

            $accountToAssign = StockholderAccount::findOrFail($request->accountToAssign);
            $assignor = $accountToAssign->stockholder->accountType === 'indv' ? $accountToAssign->stockholder->userId : $request->assignor;
            $proxy = $this->createProxyBoardOfDirector($assignor, $request);
            $history = $this->createBodProxyHistory($proxy, $request);


            ActivityController::log([
                'activityCode' => '00030',
                'remarks' => "<span class='fw-bold'>Assigned</span> BOD proxy form no
                              <span class='font-weight-bold'>" . $proxy->proxyBodFormNo . " </span> 
                              to <span class='font-weight-bold'>" . $proxy->proxy_assignee_name . "</span> --ID: " . $proxy->proxyBodId,
                'accountId' => $accountToAssign->accountId,
                'userId' => $accountToAssign->user->id,
                'proxyBodId' => $proxy->proxyBodId,
                'proxyBodHistoryId' => $history->id
            ]);


            DB::commit();

            Log::info('BOD Proxy stored successfully', [
                'proxyBodId' => $proxy->proxyBodId
            ]);

            return response()->json(['message' => 'The BOD proxy has been successfully assigned.'], 200);
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while storing BOD proxy');
            return response()->json([], 500);
        }
    }

    private function createBodProxyHistory($proxy): ProxyBoardOfDirectorHistory
    {
        return ProxyBoardOfDirectorHistory::create([
            'proxyBodId' => $proxy->proxyBodId,
            'proxyBodFormNo' => $proxy->proxyBodFormNo,
            'accountId' => $proxy->accountId,
            'assignorId' => $proxy->assignorId,
            'assigneeId' => $proxy->assigneeId,
            'assignorName' => $proxy->proxy_assignor_name,
            'assignorEmail' => $proxy->assignor->email,
            'assigneeName' => $proxy->proxy_assignee_name,
            'assigneeEmail' => $proxy->assignee->email,
            'createdBy' => Auth::user()->id,
            'createdAt' => now(),
            'updatedBy' => Auth::user()->id,
            'updatedAt' => now(),
            'status' => 'assigned',

        ]);
    }

    private function createProxyBoardOfDirector($assignor, $request): ProxyBoardOfDirector
    {

        return ProxyBoardOfDirector::create([
            'proxyBodFormNo' => $request->input('proxyFormNo'),
            'accountId' => $request->accountToAssign,
            'assignorId' => $assignor,
            'assigneeId' => $request->assignee,
            'createdBy' => Auth::user()->id
        ]);
    }

    private function validateAssignee($request): User
    {


        $assigneeUserDetails = User::whereIn('role', ['stockholder', 'corp-rep', 'non-member'])->findOrFail($request->assignee);

        if ($assigneeUserDetails->role === 'non-member') {
            $nonmemberInfo = $assigneeUserDetails->nonMemberAccount()->withTrashed()->first();

            if (!$nonmemberInfo) {
                Log::error('Non-member account not found', ['assignee' => $assigneeUserDetails->id]);
                throw new ValidationErrorException("Assignee non-member account not found.");
            }

            if ($nonmemberInfo->trashed()) {
                Log::error('Non-member account is inactive', ['assignee' => $assigneeUserDetails->id]);
                throw new ValidationErrorException("Assignment to an inactive non-member is not allowed. Please reactivate the non-member to proceed.");
            }


            if ($assigneeUserDetails->nonMemberAccount->isGM === 1) {

                Log::error('General Manager (GM) cannot be a proxyholder for the Board of Directors (BOD)', ['assignee' => $assigneeUserDetails->id]);
                throw new ValidationErrorException("The General Manager (GM) cannot be a proxyholder for the Board of Directors (BOD).");
            }
        }







        if ($assigneeUserDetails->role === 'corp-rep') {

            if ($assigneeUserDetails->email === null) {

                Log::error('Assignee corporate representative does not have an email address', ['assignee' => $assigneeUserDetails->id]);
                throw new ValidationErrorException("Proxy cannot be assigned. The assignee does not have an email address.");
            }
        }

        return $assigneeUserDetails;
    }

    private function validateAssignor($request): User
    {

        $accountToAssign = StockholderAccount::findOrFail($request->accountToAssign);

        $assignor        = $accountToAssign->stockholder->accountType === 'indv' ? $accountToAssign->stockholder->userId : $request->assignor;

        $assignorUserDetails = User::whereIn('role', ['stockholder', 'corp-rep'])->findOrFail($assignor);

        if ($assignorUserDetails->role === 'stockholder') {

            if ($assignorUserDetails->stockholder->accountNo !== $accountToAssign->stockholder->accountNo) {

                Log::error('Assignor (stockholder) account number does not match the stock being assigned', ['assignor' => $assignorUserDetails->id]);
                throw new ValidationErrorException("The assignor and the stock being assigned have different account numbers.");
            }
        }

        if ($assignorUserDetails->role === 'corp-rep') {

            $hasMatchingStockholderAccount = $accountToAssign->stockholder
                ->stockholderAccounts()
                ->where('userId', $assignor)
                ->exists();

            if (!$hasMatchingStockholderAccount) {

                Log::error('Assignor (corporate representative) account number does not match the stock being assigned', ['assignor' => $assignorUserDetails->id]);
                throw new ValidationErrorException("The assignor and the stock being assigned have different account numbers.");
            }

            if ($assignorUserDetails->email === null) {
                Log::error('Assignor corporate representative does not have an email address', ['assignor' => $assignorUserDetails->id]);
                throw new ValidationErrorException("Cannot assign proxy. The assignor does not have an email address.");
            }

            if ($assignorUserDetails->email !== $accountToAssign->user->email) {

                Log::error("Proxy assignment failed. Corporate representatives must assign the stock under their own name.", ['assignor' => $assignorUserDetails->id, 'accountEmail' => $accountToAssign->user->email]);
                throw new ValidationErrorException("Proxy assignment failed. Corporate representatives must assign the stock under their own name.");
            }
        }

        return $assignorUserDetails;
    }

    public function cancel($request, $id)
    {
        try {


            Log::info("Cancelling BOD proxy with ID: $id");
            $proxy = ProxyBoardOfDirector::findOrFail($id);

            if ($proxy->auditedBy !== null) {
                Log::error("Attempt to cancel a verified proxy.", ['proxyBodId' => $proxy->id]);
                return response()->json(['message' => 'Cannot cancel a verified proxy. Please contact the Auditor.'], 400);
            }
            DB::beginTransaction();

            $history = $this->createCancelHistory($proxy, $request);
            $this->createCancellationRecord($proxy, $request);

            ActivityController::log([
                'activityCode' => '00031',
                'remarks' => "Cancelled BOD proxy form no   
                              <span class='font-weight-bold'>" . $proxy->proxyBodFormNo . "</span> 
                              that was assigned to <span class='font-weight-bold'>" . $proxy->proxy_assignee_name . "</span> --ID: " . $proxy->proxyBodId,

                'accountId' => $proxy->accountId,
                'userId' => $proxy->stockholderAccount->user->id,
                'proxyBodId' => $id,
                'proxyBodHistoryId' => $history->id,
                'accountId' => $proxy->accountId,
                'userId' => $proxy->stockholderAccount->userId
            ]);






            ProxyBoardOfDirector::where('proxyBodId', $id)->forceDelete();
            DB::commit();
            Log::info("BOD proxy with ID: $id has been successfully cancelled");

            return response()->json(['message' => 'BOD proxy has been cancelled successfully.'], 200);
        } catch (Exception $e) {

            UtilityService::logServerError(request(), $e, 'Error occurred while cancelling BOD proxy');

            return response()->json([], 500);
        }
    }


    private function createCancellationRecord($proxy, $request): ProxyBoardOfDirectorCancelled
    {


        $now = now();
        $history = $proxy->toArray();

        unset($history['assignor']);
        unset($history['assignee']);

        $history['createdBy'] = Auth::id();
        $history['createdAt'] = $now;
        $history['updatedBy'] = Auth::id();
        $history['updatedAt'] = $now;
        $history['cancelledBy'] = Auth::id();
        $history['cancelledAt'] = $now;
        $history['reason'] = $request->reason;
        $history['remarks'] = $request->remarks;
        $history['assignorName'] = $proxy->proxy_assignor_name;
        $history['assignorEmail'] = $proxy->assignor->email;
        $history['assigneeName'] = $proxy->proxy_assignee_name;
        $history['assigneeEmail'] = $proxy->assignee->email;

        $cancelled = new ProxyBoardOfDirectorCancelled();
        $cancelled->create($history);

        return $cancelled;
    }

    private function createCancelHistory($proxy, $request): ProxyBoardOfDirectorHistory
    {

        $now = now();
        $history = $proxy->toArray();
        // $history['proxyBodHistoryId'] = $proxyBodHistoryId;

        $history['createdBy'] = Auth::id();
        $history['createdAt'] = $now;
        $history['cancelledBy'] = Auth::id();
        $history['cancelledAt'] = $now;
        $history['updatedBy'] = Auth::id();
        $history['updatedAt'] = $now;

        $history['status'] = 'cancelled';
        $history['remarks'] = $request->remarks;
        $history['reason'] = $request->reason;
        $history['assignorName'] = $proxy->proxy_assignor_name;
        $history['assignorEmail'] = $proxy->assignor->email;
        $history['assigneeName'] = $proxy->proxy_assignee_name;
        $history['assigneeEmail'] = $proxy->assignee->email;


        return ProxyBoardOfDirectorHistory::create($history);
    }

    public function audit($request, $id)
    {
        try {

            Log::info("Auditing BOD proxy with ID: $id");
            DB::beginTransaction();
            $action = $this->getAuditAction($request->input('action'));
            $proxyBod = ProxyBoardOfDirector::findOrFail($id);
            $this->validateAuditRule($proxyBod, $action);

            if ($action === 'verify') {
                $this->verifyProxy($proxyBod);
            } else {
                $this->unverifyProxy($proxyBod);
            }


            $this->logProxyAuditActivity($proxyBod, $action);
            $message = $action == 'verify'
                ? 'The proxy with form number ' . $proxyBod->proxyBodFormNo . ' has been successfully verified.'
                : 'The verified status for proxy form number ' . $proxyBod->proxyBodFormNo . ' has been successfully revoked.';


            $this->createAuditHistory($proxyBod, $action);
            Log::info("Audit history created for proxy with ID: $id", ['action' => $action]);
            DB::commit();

            Log::info("BOD proxy with ID: $id has been successfully audited", ['action' => $action]);

            return response()->json(['message' => $message], 200);
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while auditing BOD proxy');

            return response()->json([], 500);
        }
    }

    private function validateAuditRule($proxyBod, $action)
    {

        if ($proxyBod->auditedBy === null and  $action === 'unverify') {

            Log::error("Attempt to revoke verification status for a proxy that has not been verified.", ['proxyBodId' => $proxyBod->id]);
            throw new ValidationErrorException("You are trying to revoke verification status for a proxy that has not been verified.");
        }

        if ($proxyBod->auditedBy !== null and  $action === 'verify') {
            Log::error("Attempt to verify a proxy that has already been verified.", ['proxyBodId' => $proxyBod->id]);
            throw new ValidationErrorException("You are trying to verify a proxy that has already been verified.");
        }
    }

    private function createAuditHistory($proxy, $action)
    {

        $history = $proxy->toArray();

        unset($history['assignee']);
        unset($history['stockholder_account']);


        $history['assignorName'] = $proxy->proxy_assignor_name;
        $history['assignorEmail'] = $proxy->assignor->email;
        $history['assigneeName'] = $proxy->proxy_assignee_name;
        $history['assigneeEmail'] = $proxy->assignee->email;




        // $history['proxyBodHistoryId'] = $proxyBodHistoryId;
        $history['status'] = $action === 'verify' ? 'verified' : 'unverified';
        $history['remarks'] = $action === 'verify'
            ? 'Verified BOD proxy'
            : 'Revoked verified status';

        $history['updatedAt'] = now();



        return ProxyBoardOfDirectorHistory::create($history);
    }


    private function logProxyAuditActivity($proxy, $action)
    {


        $remarks = $action === 'verify'
            ? "Verified BOD proxy form <span class='font-weight-bold'>{$proxy->proxyBodFormNo}</span> that was assigned to <span class='font-weight-bold'>{$proxy->proxy_assignee_name}</span> --ID: {$proxy->proxyBodId}"
            : "Revoked verified status for BOD proxy form <span class='font-weight-bold'>{$proxy->proxyBodFormNo}</span> that was assigned to <span class='font-weight-bold'>{$proxy->proxy_assignee_name}</span> --ID: {$proxy->proxyBodId}";


        ActivityController::log([
            'activityCode' => $action === 'verify' ? '00039' : '00040',
            'remarks' => $remarks,
            'proxyBodId' => $proxy->proxyBodId,
            'userId' => $proxy->stockholderAccount->userId
        ]);
    }

    private function verifyProxy($proxy)
    {

        if (Auth::user()->cannot('verify bod proxy')) {
            Log::warning("BOD Proxy: Unauthorized verify attempt", ['userId' => Auth::id()]);
            throw new ValidationErrorException("You are not authorized to verify this BOD proxy.");
        }

        $proxy->auditedBy = Auth::id();
        $proxy->auditedAt = now();
        $proxy->save();
    }

    private function unverifyProxy($proxy)
    {

        if (Auth::user()->cannot('remove bod proxy audit')) {
            Log::warning("BOD Proxy: Unauthorized attempt to revoke verification status", ['userId' => Auth::id()]);
            throw new ValidationErrorException("You do not have permission to revoke the verified status for this BOD proxy.");
        }



        $proxy->auditedBy = null;
        $proxy->auditedAt = null;
        $proxy->save();
    }
    private function getAuditAction($action)
    {
        if (!in_array($action, [0, 1])) {
            Log::error("Invalid audit action: $action", ['action' => $action]);
            throw new ValidationErrorException("Invalid audit action.");
        }

        return $action == 1 ? 'verify' : 'unverify';
    }

    public function getProxies($request, $filter)
    {


        $proxies = ProxyBoardOfDirector::with([
            'stockholderAccount',
            'assignee',
            'assignee.stockholder',
            'assignee.stockholderAccount',
            'assignee.stockholderAccount.stockholder',
            'assignee.nonMemberAccount',
            'auditor.adminAccount' => function ($query) {
                $query->withTrashed();
            },
            'assignor.stockholder',
            'assignor.stockholderAccount',
            'assignor.stockholderAccount.stockholder',
            'assignor.nonMemberAccount',
            'createdBy',
            'usedAccount',
            'cancelledProxyBod' => function ($query) {
                $query->where('reason', 'quorum');
            }
        ])
            ->when($filter !== null, function ($query) use ($filter) {

                if ($filter === 'unverified') {
                    $query->where('auditedBy', '=', null);
                } elseif ($filter === 'verified') {
                    $query->where('auditedBy', '!=', null);
                }
            })
            ->get()->toArray();


        $proxyList = [];

        foreach ($proxies as $proxy) {

            $assignee = '';

            switch ($proxy['assignee']['role']) {

                case 'stockholder':
                    $assignee = $proxy['assignee']['stockholder']['stockholder'];
                    $assigneeAccountNo = $proxy['assignee']['stockholder']['accountNo'];

                    break;

                case 'corp-rep':
                    $assignee = $proxy['assignee']['stockholder_account']['corpRep'];
                    $assigneeAccountNo = $proxy['assignee']['stockholder_account']['accountKey'];
                    break;

                case 'non-member':
                    $assignee = $proxy['assignee']['non_member_account']['lastName'] . ', ' . $proxy['assignee']['non_member_account']['firstName'];
                    $assigneeAccountNo = $proxy['assignee']['non_member_account']['nonmemberAccountNo'];
                    break;

                default:

                    throw new Exception("Assignee is not valid.");
                    break;
            }


            switch ($proxy['assignor']['role']) {

                case 'stockholder':
                    $assignor = $proxy['assignor']['stockholder']['stockholder'];
                    $assignorAccountNo = $proxy['assignor']['stockholder']['accountNo'];


                    break;

                case 'corp-rep':
                    $assignor = $proxy['assignor']['stockholder_account']['corpRep'];
                    $assignorAccountNo = $proxy['assignor']['stockholder_account']['accountKey'];
                    break;

                default:

                    throw new Exception("Assignor is not valid.");
                    break;
            }





            $proxyList[] = array(

                'id' => $proxy['proxyBodId'],
                'accountId' => $proxy['accountId'],
                'accountNo' => $proxy['stockholder_account']['accountKey'],
                'assignee' => $assignee,
                'assigneeAccountNo' => $assigneeAccountNo,
                'assignor' => $assignor,
                'assignorAccountNo' => $assignorAccountNo,
                'proxyFormNo' => $proxy['proxyBodFormNo'],
                'isDelinquent' => $proxy['stockholder_account']['isDelinquent'] === 1 ? 'delinquent' : 'active',
                'vote' => $proxy['used_account'] === null ? 'available' : 'used',
                'audited' => $proxy['auditedBy'] === null ? '' : 'checked',
                'auditor' => $proxy['auditedBy'] === null ? '' : $proxy['auditor']['admin_account']['firstName'] . ' ' .  $proxy['auditor']['admin_account']['lastName'],
                'auditedAt' => $proxy['auditedAt'] ?? '',
                'cancelled' => $proxy['cancelled_proxy_bod']
            );
        }


        usort($proxyList, function ($a, $b) {
            return $a['assignee'] <=> $b['assignee'] ?: $a['proxyFormNo'] <=> $b['proxyFormNo'];
        });
        return $proxyList;
    }


    public function loadActiveProxies(): array
    {

        $proxies = ProxyBoardOfDirector::with([
            'assignor.stockholder',
            'assignor.stockholderAccount',
            'assignee.stockholder',
            'assignee.stockholderAccount',
            'assignee.nonMemberAccount'
        ])->get();

        $proxyholders = [];
        foreach ($proxies as $proxy) {
            switch ($proxy->assignor->role) {
                case 'stockholder':
                    $assignor = $proxy->assignor->stockholder->stockholder;
                    $assignorType = 'stockholder';
                    $assignorAccount = $proxy->assignor->stockholder->accountNo;
                    break;
                case 'corp-rep':
                    $assignor = $proxy->assignor->stockholderAccount->corpRep;
                    $assignorType = 'corp rep';
                    $assignorAccount = $proxy->assignor->stockholderAccount->accountKey;
                    break;
            }

            switch ($proxy->assignee->role) {
                case 'stockholder':
                    $assignee = $proxy->assignee->stockholder->stockholder;
                    $assigneeType = 'stockholder';
                    $assigneeAccount = $proxy->assignee->stockholder->accountNo;
                    break;
                case 'corp-rep':
                    $assignee = $proxy->assignee->stockholderAccount->corpRep;
                    $assigneeType = 'corp rep';
                    $assigneeAccount = $proxy->assignee->stockholderAccount->accountKey;
                    break;
                case 'non-member':
                    $assignee = $proxy->assignee->nonMemberAccount->lastName . ', ' . $proxy->assignee->nonMemberAccount->firstName;
                    $assigneeType = 'non-member';
                    $assigneeAccount = $proxy->assignee->nonMemberAccount->nonmemberAccountNo;
                    break;
            }

            $proxyholders[] = [
                'id' => $proxy->proxyBodId,
                'account' => $proxy->stockholderAccount->accountKey,
                'proxyBodFormNo' => $proxy->proxyBodFormNo,
                'assignor' => $assignor,
                'assignorAccount' => $assignorAccount,
                'assignorType' => $assignorType,

                'assignee' => $assignee,
                'assigneeAccount' => $assigneeAccount,
                'assigneeType' => $assigneeType,
                'status' => 'active',
                'remarks' => null
            ];
        }

        return $proxyholders;
    }


    public function loadCancelledProxies(): array
    {
        $proxies = ProxyBoardOfDirectorCancelled::with([
            'assignor.stockholder',
            'assignor.stockholderAccount',
            'assignee.stockholder',
            'assignee.stockholderAccount',
            'assignee.nonMemberAccount'
        ])->where('reason', 'quorum')->get();

        // return $proxies->toArray();

        $proxyholders = [];
        foreach ($proxies as $proxy) {
            switch ($proxy->assignor->role) {
                case 'stockholder':
                    $assignor = $proxy->assignor->stockholder->stockholder;
                    $assignorType = 'stockholder';
                    $assignorAccount = $proxy->assignor->stockholder->accountNo;
                    break;
                case 'corp-rep':
                    $assignor = $proxy->assignor->stockholderAccount->corpRep;
                    $assignorType = 'corp rep';
                    $assignorAccount = $proxy->assignor->stockholderAccount->accountKey;
                    break;
            }

            switch ($proxy->assignee->role) {
                case 'stockholder':
                    $assignee = $proxy->assignee->stockholder->stockholder;
                    $assigneeType = 'stockholder';
                    $assigneeAccount = $proxy->assignee->stockholder->accountNo;
                    break;
                case 'corp-rep':
                    $assignee = $proxy->assignee->stockholderAccount->corpRep;
                    $assigneeType = 'corp rep';
                    $assigneeAccount = $proxy->assignee->stockholderAccount->accountKey;
                    break;
                case 'non-member':
                    $assignee = $proxy->assignee->nonMemberAccount->lastName . ', ' . $proxy->assignee->nonMemberAccount->firstName;
                    $assigneeType = 'non-member';
                    $assigneeAccount = $proxy->assignee->nonMemberAccount->nonmemberAccountNo;
                    break;
            }

            $proxyholders[] = [
                'id' => $proxy->proxyBodCancelledId,
                'account' => $proxy->stockholderAccount->accountKey,
                'proxyBodFormNo' => $proxy->proxyBodFormNo,
                'assignor' => $assignor,
                'assignorAccount' => $assignorAccount,
                'assignorType' => $assignorType,
                'assignee' => $assignee,
                'assigneeAccount' => $assigneeAccount,
                'assigneeType' => $assigneeType,
                'status' => 'cancelled',
                'remarks' => $proxy->remarks
            ];
        }

        return $proxyholders;
    }




    public function getSummary()
    {
        $groupByEmail = [];

        $proxyholders = User::with([
            'collectedProxy',
            'collectedProxy.stockholderAccount:accountId,isDelinquent',
            'stockholder',
            'stockholderAccount',
            'stockholderAccount.stockholder',
            'nonMemberAccount'
        ])
            ->has('collectedProxy')
            ->get();

        $groupByEmail = [];

        foreach ($proxyholders as $proxyholder) {

            if ($proxyholder->email === null) {

                throw new Exception("Assignor's email cannot be null");
            }

            switch ($proxyholder->role) {

                case 'stockholder':
                    $userRole = 'SH';
                    $corpRep = null;
                    $stockholder = $proxyholder->stockholder->stockholder;
                    $accountNo = $proxyholder->stockholder->accountNo;
                    break;

                case 'corp-rep':
                    $userRole = 'CR';
                    $corpRep = $proxyholder->stockholderAccount->corpRep;
                    $stockholder = $proxyholder->stockholderAccount->stockholder->stockholder;
                    $accountNo = $proxyholder->stockholderAccount->accountKey;
                    break;

                case 'non-member':
                    $userRole = 'NM';
                    $corpRep = null;
                    $stockholder = $proxyholder->nonMemberAccount->lastName . ', ' . $proxyholder->nonMemberAccount->firstName;
                    $accountNo = $proxyholder->nonMemberAccount->nonmemberAccountNo;
                    break;

                default:
                    throw new Exception('Account type is not valid. Only allowed accountType can have a proxy.');
                    break;
            }


            foreach ($proxyholder->collectedProxy->toArray() as $proxy) {
                $groupByEmail[$proxyholder->email]['userId'] = $proxyholder->id;
                $groupByEmail[$proxyholder->email]['role'] = $userRole;
                $groupByEmail[$proxyholder->email]['stockholder'] = $stockholder;
                $groupByEmail[$proxyholder->email]['corpRep'] = $corpRep;
                $groupByEmail[$proxyholder->email]['accountNo'] = $accountNo;
                $groupByEmail[$proxyholder->email]['proxies'][] = $proxy;
                $groupByEmail[$proxyholder->email]['isDelinquent'][] = $proxy['stockholder_account']['isDelinquent'];
            }
        }

        // Sort by stockholder name
        uksort($groupByEmail, function ($emailA, $emailB) use ($groupByEmail) {
            $stockholderA = $groupByEmail[$emailA]['stockholder'] ?? '';
            $stockholderB = $groupByEmail[$emailB]['stockholder'] ?? '';
            return strcasecmp($stockholderA, $stockholderB);
        });

        ActivityController::log(['activityCode' => '00112']);

        return $groupByEmail;
    }

    public function getProxyList($request, $id)
    {

        Log::info("Fetching BOD proxy list for user ID: $id");
        $proxyList = [];
        $user = User::with([
            'stockholder',
            'stockholderAccount',
            'stockholderAccount.stockholder'
        ])->findOrFail($id);

        switch ($user->role) {
            case 'stockholder':
                $proxyList = ProxyBoardOfDirector::with([
                    'assignor:id,role',
                    'assignor.stockholder',
                    'assignor.stockholderAccount',
                    'assignor.stockholderAccount.stockholder',
                    'stockholderAccount.stockholder',
                    'usedAccount'

                ])->where('assigneeId', $id)->get()->toArray();

                break;

            case 'corp-rep':

                $accountNo = $user->stockholderAccount->stockholder->accountNo;

                $assigneeIds = User::whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                    $query->where('accountNo', $accountNo);
                })->where('email', $user->email)->pluck('id');


                $proxyList   = ProxyBoardOfDirector::with([
                    'assignor:id,role',
                    'assignor.stockholder',
                    'assignor.stockholderAccount',
                    'assignor.stockholderAccount.stockholder',
                    'stockholderAccount.stockholder',
                    'usedAccount'

                ])->whereIn('assigneeId', $assigneeIds)->get()->toArray();;
                Log::debug($proxyList);
                break;

            case 'non-member':
                $proxyList = ProxyBoardOfDirector::with([
                    'assignor:id, role',
                    'assignor.stockholder',
                    'assignor.stockholderAccount',
                    'assignor.stockholderAccount.stockholder',
                    'stockholderAccount.stockholder',
                    'usedAccount'


                ])->where('assigneeId', $id)->get()->toArray();

                break;
        }
        Log::info("Fetched proxy list for user ID: $id");
        return $proxyList;
    }


    public function masterlist($request)
    {
        // validate filter
        $allowed = [null, 'all', 'multiple issuance', 'cancelled'];
        $filter = $request->filter;
        if (! in_array($filter, $allowed, true)) {
            Log::error('Invalid filter value: ' . $filter, ['request' => $request->all()]);
            throw new ValidationErrorException('Invalid filter value.');
        }

        // load proxies from service
        $activeProxies = collect($this->loadActiveProxies());
        $cancelledProxies = collect($this->loadCancelledProxies());

        // merged collection (keep stable keys and make values sequential)
        $mergedAll = $activeProxies->merge($cancelledProxies)->values();

        // helper sort by account (fallback to empty string)
        $sortByAccount = function ($item) {
            return mb_strtolower($item['account'] ?? ($item['assignorAccount'] ?? ''));
        };

        // apply filter
        $merged = $mergedAll->sortBy($sortByAccount)->values();

        if ($filter === 'multiple issuance') {
            $merged = $mergedAll
                ->groupBy(function ($item) {
                    return $item['account'] ?? ($item['assignorAccount'] ?? '');
                })
                ->filter(function ($group) {
                    return $group->count() > 1;
                })
                ->flatten(1)
                ->sortBy($sortByAccount)
                ->values();
        } elseif ($filter === 'cancelled') {
            $merged = $mergedAll->filter(function ($item) {
                return isset($item['status']) && $item['status'] === 'cancelled';
            })->sortBy($sortByAccount)->values();
        }

        ActivityController::log(['activityCode' => '00114']);

        return $merged;
    }

    public function exportMasterlist($request)
    {
        try {

            $masterList = $this->masterlist($request);

            $masterlistArray = [];

            foreach ($masterList as $item) {

                $masterlistArray[] = [
                    'account' => $item['account'],
                    'formNo' => $item['proxyBodFormNo'],
                    'assignor' => $item['assignor'],
                    'assignorAccount' => $item['assignorAccount'],
                    'assignorType' => $item['assignorType'],
                    'assignee' => $item['assignee'],
                    'assigneeAccount' => $item['assigneeAccount'],
                    'assigneeType' => $item['assigneeType'],
                    'status' => $item['status'],
                    'remarks' => $item['remarks']
                ];
            }

            $filter = $request->filter === null ? 'all' : strtolower($request->filter);

            $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
            Log::info("Board of Directors Proxy: Exporting proxy masterlist", [
                "title" => 'Board of Directors Proxy Masterlist (' . $filter . ') ' . $currentDateTime . '.xlsx'
            ]);
            ActivityController::log(['activityCode' => '00127']);
            Log::info("Board of Directors Proxy: Exported masterlist", [
                "title" => 'Board of Directors Proxy Masterlist (' . $filter . ') ' . $currentDateTime . '.xlsx'
            ]);

            return Excel::download(new \App\Exports\BoardOfDirectorProxyMasterlistExport($masterlistArray), 'Board of Directors Proxy Masterlist ' . $currentDateTime . ' (' . $filter . ') ' . '.xlsx');
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while exporting Board of Directors Proxy masterlist');

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }


    public function countQuorumProxies(): int
    {

        // load proxies from service
        $activeProxies = collect($this->loadActiveProxies());
        $cancelledProxies = collect($this->loadCancelledProxies());

        // merged collection (keep stable keys and make values sequential)
        $mergedAll = $activeProxies->merge($cancelledProxies)->values();

        // Filter for accounts with multiple issuances
        $merged = $mergedAll
            ->groupBy(function ($item) {
                return $item['account'];
            })
            ->filter(function ($group) {
                return $group->count() > 1;
            })
            ->flatten(1)
            ->values();

        $uniqueAccountNoCount = $merged->unique('account')->count();

        return $uniqueAccountNoCount;
    }

    public function printProxyByAssignee($request, $id)
    {

        Log::info("Printing BOD proxy list for assignee user ID: $id");

        $proxyList = $this->getProxyList($request, $id);
        $proxyArr = [];

        foreach ($proxyList as $proxy) {

            $assignorAccountNo = $proxy['assignor']['role'] === 'stockholder'
                ? $proxy['assignor']['stockholder']['accountNo']
                : $proxy['assignor']['stockholder_account']['accountKey'];

            $assignorName = $proxy['assignor']['role'] === 'stockholder'
                ? $proxy['assignor']['stockholder']['stockholder']
                : $proxy['assignor']['stockholder_account']['corpRep'];


            $proxyArr[] = [
                'accountNo' => $proxy['stockholder_account']['accountKey'],
                'stockholder' => $proxy['stockholder_account']['stockholder']['stockholder'],
                'proxyFormNo' => $proxy['proxyBodFormNo'],
                'assignorAccountNo' => $assignorAccountNo,
                'assignorName' => $assignorName,

            ];
        }


        usort($proxyArr, function ($a, $b) {
            return $a['stockholder'] <=> $b['stockholder'] ?: $a['accountNo'] <=> $b['accountNo'];
        });


        $userInfo = User::findOrFail($id);

        ActivityController::log([
            'activityCode' => '00137',
            'remarks' => 'Printed BOD proxy list for ' . $userInfo->account_no . ' - ' . $userInfo->full_name,
            'userId' => $id,
        ]);





        $user = Auth::user();
        $generatedBy = $user->adminAccount->firstName . ' ' . $user->adminAccount->lastName;
        $generatedAt = now()->format('Y-m-d H:i:s') . ' (' . config('app.timezone') . ')';
        $footerText = 'Generated by ' . $generatedBy . ' on ' . $generatedAt;
        $pdf = PDF::loadView('prints.print_proxy_by_assignee', [
            'generatedBy' => $generatedBy,
            'generatedAt' => $generatedAt,
            'footerText' => $footerText,

            'proxyList' => $proxyArr,
            'userInfo' => $userInfo
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $pdf->setOptions(['enable_php' => true]);


        //save to server privately
        $fileName = 'VALID PROXY LIST FOR ' . $userInfo->account_no . ' - ' . $userInfo->full_name . ' AS OF ' . now()->format('Y-m-d_H:i:s') . '.pdf';



        Log::info("Printed BOD proxy list for assignee user ID: $id");


        return $pdf->stream($fileName);



        return view('prints.print_proxy_by_assignee', [
            'proxyList' => $proxyArr,
            'userInfo' => $userInfo
        ]);
    }
}
