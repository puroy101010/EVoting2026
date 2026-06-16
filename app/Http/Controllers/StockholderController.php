<?php

namespace App\Http\Controllers;


use App\Exceptions\ValidationErrorException;
use Illuminate\Http\Request;
use App\Models\Stockholder;
use Exception;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StockholderAccount;
use App\Exports\MemberExport;
use Illuminate\Support\Carbon;
use App\Http\Requests\EditStockholderRequest;
use App\Http\Requests\UpdateStockholderRequest;
use App\Http\Requests\ExportStockholderRequest;
use App\Http\Requests\GetStockholderRequest;
use App\Http\Requests\IndexStockholderRequest;
use App\Http\Requests\StoreStockholderRequest;
use App\Http\Requests\UpdateAuthSignatoryRequest;
use App\Http\Requests\UpdateCorporateRepRequest;
use App\Services\AppHelper;
use App\Services\StockholderService;
use App\Services\UtilityService;
use App\Services\OnlineAccountService;

use App\Services\StockholderImportService;

class StockholderController extends Controller
{

    protected StockholderService $stockholderService;
    protected $groupByCombinedEmail = [];


    public function __construct(StockholderService $stockholderService)
    {
        $this->stockholderService = $stockholderService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexStockholderRequest $request)
    {
        return $this->stockholderService->index();
    }

    public function store(StoreStockholderRequest $request)
    {
        return $this->stockholderService->store($request);
    }

    public function export(ExportStockholderRequest $request)
    {

        try {

            $users = User::with(
                'stockholderAccount.stockholder.user',
                'stockholderAccount.proxyBoard.assignee.stockholder',
                'stockholderAccount.proxyBoard.assignee.stockholderAccount',
                'stockholderAccount.proxyBoard.assignee.nonMemberAccount',

                'stockholderAccount.proxyAmendment.assignor.stockholder',
                'stockholderAccount.proxyAmendment.assignor.stockholderAccount',
                'stockholderAccount.proxyAmendment.assignor.nonMemberAccount'
            )->where('role', 'corp-rep')->get();




            $export = [];
            $quorumCount = 0;


            foreach ($users as $user) {

                $assignee = "";
                $assigneeAmendment = "";

                $assignor = "";
                $assignorAmendment = "";


                if ($user->stockholderAccount->proxyBoard !== null) {

                    switch ($user->stockholderAccount->proxyBoard->assignee->role) {

                        case 'stockholder':
                            $assignee = $user->stockholderAccount->proxyBoard->assignee->stockholder->accountNo . ' - ' . $user->stockholderAccount->proxyBoard->assignee->stockholder->stockholder;
                            break;

                        case 'corp-rep';
                            $assignee = $user->stockholderAccount->proxyBoard->assignee->stockholderAccount->accountKey . ' - ' . $user->stockholderAccount->proxyBoard->assignee->stockholderAccount->corpRep;
                            break;


                        case 'non-member':
                            $assignee = $user->stockholderAccount->proxyBoard->assignee->nonMemberAccount->nonmemberAccountNo . ' - ' . $user->stockholderAccount->proxyBoard->assignee->nonMemberAccount->firstName . ' ' . $user->stockholderAccount->proxyBoard->assignee->nonMemberAccount->lastName;
                            break;

                        default:
                            break;
                    }

                    switch ($user->stockholderAccount->proxyBoard->assignor->role) {

                        case 'stockholder':
                            $assignor = $user->stockholderAccount->proxyBoard->assignor->stockholder->accountNo . ' - ' . $user->stockholderAccount->proxyBoard->assignor->stockholder->stockholder;
                            break;

                        case 'corp-rep';
                            $assignor = $user->stockholderAccount->proxyBoard->assignor->stockholderAccount->accountKey . ' - ' . $user->stockholderAccount->proxyBoard->assignor->stockholderAccount->corpRep;
                            break;


                        case 'non-member':
                            $assignor = $user->stockholderAccount->proxyBoard->assignor->nonMemberAccount->nonmemberAccountNo . ' - ' . $user->stockholderAccount->proxyBoard->assignor->nonMemberAccount->firstName . ' ' . $user->stockholderAccount->proxyBoard->assignor->nonMemberAccount->lastName;
                            break;

                        default:
                            break;
                    }
                }




                if ($user->stockholderAccount->proxyAmendment !== null) {

                    switch ($user->stockholderAccount->proxyAmendment->assignee->role) {

                        case 'stockholder':
                            $assigneeAmendment = $user->stockholderAccount->proxyAmendment->assignee->stockholder->accountNo . ' - ' . $user->stockholderAccount->proxyAmendment->assignee->stockholder->stockholder;
                            break;

                        case 'corp-rep';
                            $assigneeAmendment = $user->stockholderAccount->proxyAmendment->assignee->stockholderAccount->accountKey . ' - ' . $user->stockholderAccount->proxyAmendment->assignee->stockholderAccount->corpRep;
                            break;

                        case 'non-member':
                            $assigneeAmendment = $user->stockholderAccount->proxyAmendment->assignee->nonMemberAccount->nonmemberAccountNo . ' - ' . $user->stockholderAccount->proxyAmendment->assignee->nonMemberAccount->firstName . ' ' . $user->stockholderAccount->proxyAmendment->assignee->nonMemberAccount->lastName;
                            break;

                        default:
                            break;
                    }

                    switch ($user->stockholderAccount->proxyAmendment->assignor->role) {

                        case 'stockholder':
                            $assignorAmendment = $user->stockholderAccount->proxyAmendment->assignor->stockholder->accountNo . ' - ' . $user->stockholderAccount->proxyAmendment->assignor->stockholder->stockholder;
                            break;

                        case 'corp-rep';
                            $assignorAmendment = $user->stockholderAccount->proxyAmendment->assignor->stockholderAccount->accountKey . ' - ' . $user->stockholderAccount->proxyAmendment->assignor->stockholderAccount->corpRep;
                            break;

                        case 'non-member':
                            $assignorAmendment = $user->stockholderAccount->proxyAmendment->assignor->nonMemberAccount->nonmemberAccountNo . ' - ' . $user->stockholderAccount->proxyAmendment->assignor->nonMemberAccount->firstName . ' ' . $user->stockholderAccount->proxyAmendment->assignor->nonMemberAccount->lastName;
                            break;

                        default:
                            break;
                    }
                }


                $proxyFormNo = $user->stockholderAccount->proxyBoard === null ? '' : $user->stockholderAccount->proxyBoard->proxyBodFormNo;
                $refNo = $user->stockholderAccount->proxyAmendment === null ? '' : 'A-' . $user->stockholderAccount->proxyAmendment->proxyAmendmentFormNo;

                $quorum = $proxyFormNo !== '' || $refNo !== '' ? 1 : 0;

                $quorumCount = $quorumCount + $quorum;

                $export[] = array(
                    'stockholder' => $user->stockholderAccount->stockholder->stockholder,
                    'accountNo' => $user->stockholderAccount->stockholder->accountNo,
                    'suffix' => $user->stockholderAccount->suffix,
                    'accountKey' => $user->stockholderAccount->accountKey,
                    'accountType' => $user->stockholderAccount->stockholder->accountType,
                    'email' => $user->stockholderAccount->stockholder->user->email,
                    'voteInPerson' => $user->stockholderAccount->stockholder->voteInPerson,
                    'authorizedSignatory' => $user->stockholderAccount->authorizedSignatory,
                    'corporateRepresentative' => $user->stockholderAccount->corpRep,
                    'corpRepEmail' => $user->email,
                    'delinquent' => $user->stockholderAccount->isDelinquent === 1 ? 'delinquent' : 'active',

                    'proxyFormNo' => $proxyFormNo,
                    'assignee' => $assignee,
                    'assignor' => $assignor,

                    'proxyAmendmentFormNo' => $refNo,
                    'assigneeAmendment' => $assigneeAmendment,
                    'assignorAmendment' => $assignorAmendment,

                    'quorum' => $quorum


                );
            }

            $export[] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $quorumCount);

            $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

            Log::info("Expoerted Stockholders", ["user" => Auth()->user(), "title" => 'Members ' . $currentDateTime . '.xlsx']);

            ActivityController::log(['activityCode' => '00105']);

            return Excel::download(new MemberExport($export), 'Members ' . $currentDateTime . '.xlsx');
        } catch (Exception $e) {
            Log::critical($e);
        }
    }


    //  done 2023-08-27
    public function show(Request $request)
    {

        try {

            $stockholder = Stockholder::with('user')->where('accountNo', $request->account_no)->first();

            $suffixes = [];

            if ($stockholder !== null) {

                $suffixes = $stockholder->stockholderAccounts->pluck('suffix');
            }

            return response()->json(['stockholder' => $stockholder, 'suffixes' => $suffixes]);
        } catch (Exception $e) {

            Log::error($e);

            return response()->json([], 500);
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EditStockholderRequest $request, int $id)
    {
        try {

            $userInfo = User::with([
                'stockholder',
                'stockholderAccount',
                'stockholderAccount.stockholder',
                'stockholderAccount.stockholder.user:id,email'
            ])
                ->selectraw('id, email, role')
                ->findOrFail($id);

            return $userInfo;
        } catch (Exception $e) {

            AppHelper::logServerError("Error fetching stockholder for edit", $e, ['id' => $id]);
            return response()->json(['message' => 'Error fetching stockholder for edit'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStockholderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStockholderRequest $request)
    {
        return $this->stockholderService->update($request);
    }


    public function loadAssigneeOptions()
    {
        return $this->stockholderService->loadAssigneeOptions();
    }


    public function load_filter_data_users()
    {
        try {

            $users = User::with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder')
                ->whereIn('role', ['stockholder', 'corp-rep'])
                ->get()
                ->map(function ($user) {

                    $user['full_name'] = $user->getFullNameAttribute();
                    $user['account_no'] = $user->getaccountNoAttribute();
                    $user['account_key'] = $user->getAccountKeyAttribute();

                    return $user;
                });


            return response()->json(['users' => $users], 200);
        } catch (Exception $e) {

            Log::error($e);
            return response()->json([], 500);
        }
    }




    public function loadStockholders(GetStockholderRequest $request)
    {

        try {

            $accountNo = $request->input('accounts');
            $accountType = $request->input('account_type');
            $role = $request->input('role');
            $status = $request->input('status');
            $proxy = $request->input('proxy');
            $proxyAssignee = $request->input('proxy_assignee');

            $members = User::leftJoin('stockholders', 'stockholders.userId', '=', 'users.id')
                ->leftJoin('stockholder_accounts', 'stockholder_accounts.userId', '=', 'users.id')
                ->leftJoin("stockholders as SH", "SH.stockholderId", '=', "stockholder_accounts.stockholderId")
                ->selectRaw('users.*, if(users.role="stockholder",stockholders.stockholder,SH.stockholder) as orderByStockholder, if(users.role="stockholder",stockholders.accountNo,stockholder_accounts.accountKey) as orderByAccountNo')
                ->with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder', 'stockholderAccount.stockholder.user', 'collectedProxy')
                ->where('users.role', 'corp-rep')
                ->when($accountNo, function ($query, $accountNo) {
                    return $query->where(function ($query) use ($accountNo) {
                        $query->whereHas('stockholder', function ($subquery) use ($accountNo) {
                            $subquery->where('accountNo', $accountNo);
                        })->orWhereHas('stockholderAccount.stockholder', function ($subquery) use ($accountNo) {
                            $subquery->where('accountNo', $accountNo);
                        });
                    });
                })
                ->when($accountType, function ($query, $accountType) { // Check if $accountType is not null
                    return $query->where(function ($query) use ($accountType) {
                        $query->whereHas('stockholder', function ($subquery) use ($accountType) {
                            $subquery->where('accountType', $accountType);
                        })->orWhereHas('stockholderAccount.stockholder', function ($subquery) use ($accountType) {
                            $subquery->where('accountType', $accountType);
                        });
                    });
                })
                ->when($status !== null, function ($query) use ($status) {
                    return $query->whereHas('stockholderAccount', function ($subquery) use ($status) {
                        $subquery->where('isDelinquent', $status);
                    });
                })
                ->when($proxy !== null, function ($query) use ($proxy) {
                    if ($proxy == 1) {
                        return $query->whereHas('stockholderAccount', function ($subquery) {
                            $subquery->whereHas('proxyBoard');
                        });
                    } else {
                        return $query->whereHas('stockholderAccount', function ($subquery) {
                            $subquery->whereDoesntHave('proxyBoard');
                        });
                    }
                })
                ->when($proxyAssignee !== null, function ($query) use ($proxyAssignee) {
                    return $query->where(function ($query) use ($proxyAssignee) {
                        if ($proxyAssignee == 1) {
                            $query->whereHas('collectedProxy');
                        } else {
                            $query->whereDoesntHave('collectedProxy');
                        }
                    });
                })
                ->orderBy('orderByStockholder')
                ->orderBy('orderByAccountNo')
                ->paginate($request->per_page);

            return response()->json(['data' => $members], 200);
        } catch (Exception $e) {
            AppHelper::logServerError("Error loading stockholders", $e);
            return response()->json([], 500);
        }
    }



    public function import()
    {
        return (new StockholderImportService())->import();
    }












    public function editCorpRepresentative(int $id)
    {
        return $this->stockholderService->editCorpRepresentative($id);
    }

    public function updateCorporateRep(UpdateCorporateRepRequest $request, int $id)
    {
        return $this->stockholderService->updateCorporateRep($request, $id);
    }


    public function editAuthSignatory(int $id)
    {
        return $this->stockholderService->editAuthSignatory($id);
    }

    public function updateAuthSignatory(UpdateAuthSignatoryRequest $request, int $id)
    {
        return $this->stockholderService->updateAuthSignatory($request, $id);
    }
}
