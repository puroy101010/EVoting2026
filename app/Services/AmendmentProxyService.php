<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Models\ProxyAmendment;
use App\Models\ProxyAmendmentCancelled;
use Exception;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProxyAmendmentHistory;
use App\Models\StockholderAccount;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AmendmentProxyService
{

    protected $settings = [];
    protected DateTime $now;
    protected ProxyService $proxyService;

    public function __construct()
    {
        $this->settings = ConfigService::getConfig();
        $this->now = new DateTime();
        $this->proxyService = new ProxyService();
    }


    public function index(Request $request)
    {
        try {

            if (!Auth::user()->can('view active amendment proxy')) {
                Log::warning("Amendment Proxy: Unauthorized access attempt to view active Amendment proxies");
                return view('errors.response', [
                    'code' => 403,
                    'message' => 'You do not have permission to view active Amendment proxies.'
                ]);
            }

            $filter =  $this->validateIndexFilter($request);


            Log::info("Amendment Proxy: Active Amendment Proxy accessed");


            ActivityController::log(['activityCode' => '00109']);
            return view('admin.proxy_amendments', [
                'proxyholders' => $this->getProxies($request, $filter),
                'filter' => $filter

            ]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Error fetching Amendment proxyholders");
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    public function exportActiveProxies(Request $request)
    {
        $filter =  $this->validateIndexFilter($request);

        $activeProxies =  $this->getProxies($request, $filter);

        $filter = $request->filter === null ? 'all' : strtolower($request->filter);

        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        Log::info("Amendment Proxy: Exporting active proxies", [
            "title" => 'Amendment Proxy Active Proxies (' . $filter . ') ' . $currentDateTime . '.xlsx'
        ]);
        ActivityController::log(['activityCode' => '00130']);
        Log::info("Amendment Proxy: Exported active proxies", [
            "title" => 'Amendment Proxy Active Proxies (' . $filter . ') ' . $currentDateTime . '.xlsx'
        ]);

        return Excel::download(new \App\Exports\AmendmentProxyActiveExport($activeProxies), 'Amendment Proxy Active Proxies ' . $currentDateTime . ' (' . $filter . ') ' . '.xlsx');
    }

    private function validateIndexFilter(Request $request)
    {

        if (!in_array($request->filter, [null, 'all', 'verified', 'unverified'])) {
            Log::error("Invalid filter value: " . $request->filter);
            throw new ValidationErrorException("Invalid filter value.");
        }

        return $request->filter;
    }

    public function store(Request $request)
    {
        try {

            $proxyService = new ProxyService();

            DB::beginTransaction();

            $assignorUser = $proxyService->validateAssignor($request);
            $assigneeUser = $proxyService->validateAssignee($request);

            $assignorUserId = $assignorUser->id;


            $accountToAssign = StockholderAccount::findOrFail($request->accountToAssign);
            $createdProxy = $this->createProxyAmendment($assignorUserId, $assigneeUser, $accountToAssign);
            $createdHistory = $this->createAmendmentProxyHistory($createdProxy, $request);

            $remarks = "<span class='fw-bold'>Assigned</span> Amendment proxy form <span class='fw-bold'>" . $createdProxy->proxyAmendmentFormNo . "</span> to <span class='fw-bold'>" . $createdProxy->proxy_assignee_name . "</span> (" . $assigneeUser->email . ") - ID: " . $createdProxy->proxyAmendmentId;

            ActivityController::log([
                'activityCode' => '00099',
                'remarks' => $remarks,
                'accountId' => $accountToAssign->accountId,
                'userId' => $accountToAssign->user->id,
                'proxyAmendmentId' => $createdProxy->proxyAmendmentId,
                'proxyAmendmentHistoryId' => $createdHistory->id
            ]);

            DB::commit();

            return response()->json(['message' => 'The amendment proxy has been successfully assigned.'], 200);
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            AppHelper::logServerError("Error occurred while storing amendment proxy", $e);
            return response()->json([], 500);
        }
    }

    private function createAmendmentProxyHistory($proxy, $request): ProxyAmendmentHistory
    {


        Log::debug('Creating amendment proxy history', [
            'assignee' => $proxy->assignee
        ]);

        return ProxyAmendmentHistory::create([
            'proxyAmendmentId' => $proxy->proxyAmendmentId,
            'proxyAmendmentFormNo' => $proxy->proxyAmendmentFormNo,
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



    /**
     * Create a new proxy amendment record
     *
     * Generates a proxy amendment with a unique form number derived from the account key,
     * associates it with the provided assignor and assignee, and records the creator.
     *
     * @param int $assignorUserId The user ID of the assignor
     * @param User $assigneeUser The user object of the assignee
     * @param StockholderAccount $accountToAssign The stockholder account for this proxy assignment
     *
     * @return ProxyAmendment The newly created proxy amendment record
     */

    private function createProxyAmendment(int $assignorUserId, User $assigneeUser, StockholderAccount $accountToAssign): ProxyAmendment
    {

        return ProxyAmendment::create([
            'proxyAmendmentFormNo' => 'A-' . $accountToAssign->accountKey,
            'accountId' => $accountToAssign->accountId,
            'assignorId' => $assignorUserId,
            'assigneeId' => $assigneeUser->id,
            'assigneeEmail' => $assigneeUser->email,
            'createdBy' => Auth::user()->id
        ]);
    }




    public function cancel(Request $request, int $id)
    {
        try {

            $proxy = ProxyAmendment::find($id);

            if (!$proxy) {
                return response()->json(['message' => 'Amendment proxy not found.'], 404);
            }

            if ($proxy->auditedBy !== null) {
                return response()->json(['message' => 'Cannot cancel a verified proxy. Please contact the Auditor.'], 400);
            }

            DB::beginTransaction();

            $history = $this->createCancelHistory($proxy, $request);

            $this->createCancellationRecord($proxy, $request);

            ActivityController::log([
                'activityCode' => '00100',
                'remarks' => "<span class='text-danger fw-bold'>Cancelled</span> Amendment proxy 
                              <span class='font-weight-bold'>(" . $proxy->proxyAmendmentFormNo . ")</span> 
                              that was assigned to <span class='font-weight-bold'>" . $proxy->proxy_assignee_name . "(" . $proxy->assignee->email . ")</span> --ID: " . $proxy->proxyAmendmentId,

                'accountId' => $proxy->accountId,
                'userId' => $proxy->stockholderAccount->user->id,
                'proxyAmendmentId' => $id,
                'proxyAmendmentHistoryId' => $history->id,
            ]);

            ProxyAmendment::where('proxyAmendmentId', $id)->forceDelete();
            DB::commit();
            return response()->json(['message' => 'Amendment proxy has been cancelled successfully.'], 200);
        } catch (Exception $e) {

            AppHelper::logServerError("Error occurred while cancelling amendment proxy with ID: $id", $e);

            return response()->json([], 500);
        }
    }

    private function createCancellationRecord(ProxyAmendment $proxy, Request $request): ProxyAmendmentCancelled
    {

        $history = $proxy->toArray();

        unset($history['assignor']);
        unset($history['assignee']);

        $history['createdBy'] = Auth::id();
        $history['createdAt'] = $this->now;
        $history['updatedBy'] = Auth::id();
        $history['updatedAt'] = $this->now;
        $history['cancelledBy'] = Auth::id();
        $history['cancelledAt'] = $this->now;
        $history['reason'] = $request->reason;
        $history['remarks'] = $request->remarks;
        $history['assignorName'] = $proxy->proxy_assignor_name;
        $history['assignorEmail'] = $proxy->assignor->email;
        $history['assigneeName'] = $proxy->proxy_assignee_name;
        $history['assigneeEmail'] = $proxy->assignee->email;

        $cancelled = new ProxyAmendmentCancelled();
        $cancelled->create($history);

        return $cancelled;
    }

    private function createCancelHistory(ProxyAmendment $proxy, Request $request): ProxyAmendmentHistory
    {

        $history = $proxy->toArray();

        $history['createdBy'] = Auth::id();
        $history['createdAt'] = $this->now;
        $history['cancelledBy'] = Auth::id();
        $history['cancelledAt'] = $this->now;
        $history['updatedBy'] = Auth::id();
        $history['updatedAt'] = $this->now;

        $history['status'] = 'cancelled';
        $history['remarks'] = $request->remarks;
        $history['reason'] = $request->reason;
        $history['assignorName'] = $proxy->proxy_assignor_name;
        $history['assignorEmail'] = $proxy->assignor->email;
        $history['assigneeName'] = $proxy->proxy_assignee_name;
        $history['assigneeEmail'] = $proxy->assignee->email;


        return ProxyAmendmentHistory::create($history);
    }

    public function audit(Request $request, int $id)
    {
        try {

            DB::beginTransaction();

            $action = $this->proxyService->getAuditAction($request->input('action'));
            $proxyAmendment = ProxyAmendment::findOrFail($id);

            $this->proxyService->validateAuditRule($proxyAmendment, $action);

            $this->executeAuditAction($proxyAmendment, $action);
            $this->logProxyAuditActivity($proxyAmendment, $action);

            $message = $action == 'verify'
                ? 'The proxy with form number ' . $proxyAmendment->proxyAmendmentFormNo . ' has been successfully verified.'
                : 'The verified status for proxy form number ' . $proxyAmendment->proxyAmendmentFormNo . ' has been successfully revoked.';

            $this->createAuditHistory($proxyAmendment, $action);

            DB::commit();

            return response()->json(['message' => $message], 200);
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while auditing Amendment proxy');

            return response()->json([], 500);
        }
    }

    private function executeAuditAction(ProxyAmendment $proxyAmendment, string $action)
    {
        $proxyService = new ProxyService();

        if ($action === 'verify') {
            $proxyService->verifyProxy($proxyAmendment);
            return;
        }

        $proxyService->unverifyProxy($proxyAmendment);
    }



    private function createAuditHistory(ProxyAmendment $proxy, string $action)
    {

        $history = $proxy->toArray();

        unset($history['assignee']);
        unset($history['stockholder_account']);

        $history['assignorName'] = $proxy->proxy_assignor_name;
        $history['assignorEmail'] = $proxy->assignor->email;
        $history['assigneeName'] = $proxy->proxy_assignee_name;
        $history['assigneeEmail'] = $proxy->assignee->email;
        $history['status'] = $action === 'verify' ? 'verified' : 'unverified';
        $history['remarks'] = $action === 'verify'
            ? 'Verified Amendment proxy'
            : 'Revoked verification status';

        $history['updatedAt'] = now();

        return ProxyAmendmentHistory::create($history);
    }


    private function logProxyAuditActivity($proxy, $action)
    {

        $remarks = $action === 'verify'
            ? "Verified Amendment proxy form <span class='font-weight-bold'>{$proxy->proxyAmendmentFormNo}</span> that was assigned to {$proxy->proxy_assignee_name} --ID: {$proxy->proxyAmendmentId}"
            : "Revoked verified status for Amendment proxy form <span class='font-weight-bold'>{$proxy->proxyAmendmentFormNo}</span> that was assigned to <span class='font-weight-bold'>{$proxy->proxy_assignee_name}</span> --ID: {$proxy->proxyAmendmentId}";

        ActivityController::log([
            'activityCode' => $action === 'verify' ? '00039' : '00040',
            'remarks' => $remarks,
            'proxyAmendmentId' => $proxy->proxyAmendmentId,
            'userId' => $proxy->stockholderAccount->userId
        ]);
    }

    public function getProxies($request, string $filter)
    {

        $proxies = ProxyAmendment::with([
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
            'cancelledProxyAmendment' => function ($query) {
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
                    $assignee = $proxy['assignee']['non_member_account']['firstName'] . ' ' . $proxy['assignee']['non_member_account']['lastName'];
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

                'id' => $proxy['proxyAmendmentId'],
                'accountId' => $proxy['accountId'],
                'accountNo' => $proxy['stockholder_account']['accountKey'],
                'assignee' => $assignee,
                'assigneeAccountNo' => $assigneeAccountNo,
                'assignor' => $assignor,
                'assignorAccountNo' => $assignorAccountNo,
                'proxyFormNo' => $proxy['proxyAmendmentFormNo'],
                'isDelinquent' => $proxy['stockholder_account']['isDelinquent'] === 1 ? 'delinquent' : 'active',
                'vote' => $proxy['used_account'] === null ? 'available' : 'used',
                'audited' => $proxy['auditedBy'] === null ? '' : 'checked',
                'auditor' => $proxy['auditedBy'] === null ? '' : $proxy['auditor']['admin_account']['firstName'] . ' ' .  $proxy['auditor']['admin_account']['lastName'],
                'auditedAt' => $proxy['auditedAt'] ?? '',
                'cancelled' => $proxy['cancelled_proxy_amendment']

            );
        }

        // Sort proxyList by assignor ascending
        usort($proxyList, function ($a, $b) {
            return strcmp($a['assignor'], $b['assignor']);
        });

        return $proxyList;
    }


    public function loadActiveProxies(): array
    {

        $proxies = ProxyAmendment::with([
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
                    $assignee = $proxy->assignee->nonMemberAccount->firstName;
                    $assigneeType = 'non-member';
                    $assigneeAccount = $proxy->assignee->nonMemberAccount->nonmemberAccountNo;
                    break;
            }

            $proxyholders[] = [
                'id' => $proxy->proxyAmendmentId,
                'account' => $proxy->stockholderAccount->accountKey,
                'proxyAmendmentFormNo' => $proxy->proxyAmendmentFormNo,
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
        $proxies = ProxyAmendmentCancelled::with([
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
                    $assignee = $proxy->assignee->nonMemberAccount->firstName;
                    $assigneeType = 'non-member';
                    $assigneeAccount = $proxy->assignee->nonMemberAccount->nonmemberAccountNo;
                    break;
            }

            $proxyholders[] = [
                'id' => $proxy->proxyAmendmentCancelledId,
                'account' => $proxy->stockholderAccount->accountKey,
                'proxyAmendmentFormNo' => $proxy->proxyAmendmentFormNo,
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
            'collectedProxyAmendment',
            'collectedProxyAmendment.stockholderAccount:accountId,isDelinquent',
            'stockholder',
            'stockholderAccount',
            'stockholderAccount.stockholder',
            'nonMemberAccount'
        ])
            ->has('collectedProxyAmendment')
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
                    $stockholder = $proxyholder->nonMemberAccount->firstName . ' ' . $proxyholder->nonMemberAccount->lastName;
                    $accountNo = $proxyholder->nonMemberAccount->nonmemberAccountNo;
                    break;

                default:
                    throw new Exception('Account type is not valid. Only allowed accountType can have a proxy.');
                    break;
            }


            foreach ($proxyholder->collectedProxyAmendment->toArray() as $proxy) {
                $groupByEmail[$proxyholder->email]['userId'] = $proxyholder->id;
                $groupByEmail[$proxyholder->email]['role'] = $userRole;
                $groupByEmail[$proxyholder->email]['stockholder'] = $stockholder;
                $groupByEmail[$proxyholder->email]['corpRep'] = $corpRep;
                $groupByEmail[$proxyholder->email]['accountNo'] = $accountNo;
                $groupByEmail[$proxyholder->email]['proxies'][] = $proxy;
                $groupByEmail[$proxyholder->email]['isDelinquent'][] = $proxy['stockholder_account']['isDelinquent'];
            }
        }

        ActivityController::log(['activityCode' => '00110']);

        return $groupByEmail;
    }

    public function getProxyList($request, $id)
    {

        Log::info("Fetching amendment proxy list for user ID: $id");
        $proxyList = [];
        $user = User::with([
            'stockholder',
            'stockholderAccount',
            'stockholderAccount.stockholder'
        ])->findOrFail($id);

        switch ($user->role) {
            case 'stockholder':
                $proxyList = ProxyAmendment::with([
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


                $proxyList   = ProxyAmendment::with([
                    'assignor:id,role',
                    'assignor.stockholder',
                    'assignor.stockholderAccount',
                    'assignor.stockholderAccount.stockholder',
                    'stockholderAccount.stockholder',
                    'usedAccount'

                ])->whereIn('assigneeId', $assigneeIds)->get()->toArray();;

                break;

            case 'non-member':

                Log::info("Fetching proxy list for non-member user ID: $id");
                $proxyList = ProxyAmendment::with([
                    'assignor:id,role',
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

        // summary counts
        $summary = [
            'total' => $mergedAll->count(),
            'active' => $activeProxies->count(),
            'cancelled' => $cancelledProxies->count(),
            'multiple_issuance' => $mergedAll->groupBy(function ($item) {
                return $item['account'] ?? ($item['assignorAccount'] ?? '');
            })->filter(function ($g) {
                return $g->count() > 1;
            })->count(),
        ];


        ActivityController::log(['activityCode' => '00115']);
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
                    'formNo' => $item['proxyAmendmentFormNo'],
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
            Log::info("Amendment Proxy: Exporting proxy masterlist", [
                "title" => 'Amendment Proxy Masterlist ' . $currentDateTime . ' (' . $filter . ') ' . '.xlsx'
            ]);
            ActivityController::log(['activityCode' => '00128']);
            Log::info("Amendment Proxy: Exported proxy masterlist", [
                "title" => 'Amendment Proxy Masterlist ' . $currentDateTime . ' (' . $filter . ') ' . '.xlsx'
            ]);

            return Excel::download(new \App\Exports\AmendmentProxyMasterlistExport($masterlistArray), 'Amendment Masterlist ' . $currentDateTime . ' (' . $filter . ') ' . '.xlsx');
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error occurred while exporting Amendment proxy masterlist');

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

        // Count unique accountNo after filter
        $uniqueAccountNoCount = $merged->unique('account')->count();

        return $uniqueAccountNoCount;
    }
}
