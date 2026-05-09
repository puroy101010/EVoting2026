<?php

namespace App\Http\Controllers;


use App\Exceptions\ValidationErrorException;
use Illuminate\Http\Request;
use App\Models\Stockholder;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StockholderAccount;
use App\Exports\MemberExport;
use Illuminate\Support\Carbon;
use App\Http\Requests\EditStockholderRequest;
use App\Http\Requests\ExportStockholderRequest;
use App\Http\Requests\GetStockholderRequest;
use App\Http\Requests\IndexStockholderRequest;
use App\Http\Requests\StoreStockholderRequest;
use App\Services\AppHelper;
use App\Services\StockholderService;
use App\Services\UtilityService;

class StockholderController extends Controller
{

    protected $stockholderService;
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
        try {

            Log::info("Loading stockholders page");

            return view('admin.members', [
                'title' => 'Stockholders'
            ]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error loading stockholders page");
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }





    //  done 2023-08-30
    public function store(StoreStockholderRequest $request)
    {


        return $this->stockholderService->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


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
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    // done 2023--8-28
    public function edit(EditStockholderRequest $request, $id)
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

            AppHelper::logServerError("");

            return response()->json([], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EditStockholderRequest $request)
    {
        return $this->stockholderService->update($request);
    }




    // done 2023-08-29




    // done 2023-08-29



    // done 2023-08-29





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



    // done 2023-08-27
    public function load_option_assignees()
    {
        try {

            $assignees = User::with([
                'stockholder',
                'stockholderAccount',
                'stockholderAccount.stockholder',
                'nonMemberAccount' => function ($query) {
                    $query->withTrashed();
                }
            ])
                ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                ->get()
                ->toArray();

            $assineeList = [];

            foreach ($assignees as $assignee) {

                switch ($assignee['role']) {

                    case 'stockholder':

                        $assineeList[] = array(
                            'id' => $assignee['id'],
                            'accountNo' => $assignee['stockholder']['accountNo'],
                            'stockholder' => $assignee['stockholder']['stockholder'] . ' (SH/CS)',
                            'role' => $assignee['role'],
                            'disabled' => false
                        );

                        break;

                    case 'corp-rep':


                        if ($assignee['stockholder_account']['stockholder']['accountType'] === 'corp') {

                            $assineeList[] = array(
                                'id' => $assignee['id'],
                                'accountNo' => $assignee['stockholder_account']['accountKey'],
                                'stockholder' => $assignee['stockholder_account']['stockholder']['stockholder'] . ' | ' . ($assignee['stockholder_account']['corpRep'] ?? '-no corp rep-') . ' (CR)',
                                'role' => $assignee['role'],
                                'disabled' => $assignee['email'] === null ? 'disabled' : ''
                            );
                        }

                        break;

                    case 'non-member':

                        $assineeList[] = array(
                            'id' => $assignee['id'],
                            'accountNo' => $assignee['non_member_account']['nonmemberAccountNo'],
                            'stockholder' => $assignee['non_member_account']['firstName'] . ' ' . $assignee['non_member_account']['lastName'] . ' (NM)',
                            'role' => $assignee['role'],
                            'disabled' => false
                        );

                        break;
                }
            }


            usort($assineeList, function ($a, $b) {
                return $a['accountNo'] <=> $b['accountNo']; // Sort in ascending order
            });

            return response()->json(['assignees' => $assineeList]);
        } catch (Exception $e) {

            Log::error("Error loading option assignees: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,


            ]);
            return response()->json([], 500);
        }
    }


    // done 2023-08-29
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



    // done 2023-08-29
    public function load_stockholders(GetStockholderRequest $request)
    {

        try {

            $accountNo      = $request->input('accounts');
            $accountType    = $request->input('account_type');
            $role           = $request->input('role');
            $status         = $request->input('status');
            $proxy          = $request->input('proxy');
            $proxyAssignee  = $request->input('proxy_assignee');

            $members = User::leftJoin('stockholders', 'stockholders.userId', '=', 'users.id')
                ->leftJoin('stockholder_accounts', 'stockholder_accounts.userId', '=', 'users.id')
                ->leftJoin("stockholders as SH", "SH.stockholderId", '=', "stockholder_accounts.stockholderId")
                ->selectRaw('users.*, if(users.role="stockholder",stockholders.stockholder,SH.stockholder) as orderByStockholder, if(users.role="stockholder",stockholders.accountNo,stockholder_accounts.accountKey) as orderByAccountNo')
                ->with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder', 'stockholderAccount.stockholder.user', 'collectedProxy')
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

                ->when($role !== null, function ($query) use ($role) {
                    return $query->where('role', $role);
                }, function ($query) {
                    return $query->whereIn('role', ['stockholder', 'corp-rep']);
                })
                ->orderBy('orderByStockholder')
                ->orderBy('orderByAccountNo')
                ->paginate($request->per_page);




            return response()->json(['data' => $members], 200);
        } catch (Exception $e) {

            Log::error("Error fetching stockholders: " . $e->getMessage(), [
                "error" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "exception" => $e,
                "request" => $request->all()
            ]);

            return response()->json([], 500);
        }
    }



    public function import(Request $request)
    {

        try {


            // if ($request->hasFile('excel_member')) {

            // $file = $request->file('excel_member');
            // $path = $file->getRealPath();

            $path = storage_path('app/sample template.xlsx');
            $data = Excel::toArray([], $path);
            $data = array_slice($data[0], 1); // Remove the first row from the data
            $now = now();




            $existingStockholders = $this->checkForExistingStockholders();
            $groupedByAccountNo = $this->groupByAccountNo($data);


            $groupByAccountNoWithStockholderRoleRow = [];

            foreach ($groupedByAccountNo as $keyAccountNo => $accounts) {

                $firstAccount = $accounts[0];

                $accountDetailsForStockholder = array_merge(
                    $firstAccount,
                    [
                        'combinedEmail' => trim($firstAccount['email']) === "" ? null : trim(strtolower($firstAccount['email'])),
                        // 'accountKey' => $keyAccountNo,
                        'role'          => 'stockholder'
                    ]
                );

                $groupByAccountNoWithStockholderRoleRow[$keyAccountNo][] = $accountDetailsForStockholder;

                $suffixPool = [];

                foreach ($accounts as $account) {

                    // validation in the existing account
                    if (array_key_exists($keyAccountNo, $existingStockholders)) {

                        $this->validateAndCompareExistingAndImportFile($existingStockholders, $keyAccountNo, $firstAccount);
                    }

                    $this->validateUniqueness($account, $firstAccount, $keyAccountNo);

                    if (in_array($account['suffix'], $suffixPool)) {
                        Log::error("The account number " . $account['accountNo'] . " has a duplicate suffix.");
                        throw new ValidationErrorException("The account number " . $account['accountNo'] . " has a duplicate suffix.");
                    }



                    array_push($suffixPool, $account['suffix']);

                    $account['combinedEmail'] = trim($account['corpRepEmail']) === "" ? null : trim(strtolower($account['corpRepEmail']));

                    $groupByAccountNoWithStockholderRoleRow[$account['accountNo']][] = $account;
                }
            }




            $insertUser = [];
            $insertStockholder = [];
            $insertStockholderAccount = [];
            $groupByCorpRepEmail = [];


            $lastUserId = User::max('id');

            foreach ($groupByAccountNoWithStockholderRoleRow as $keyAccountNo => $accounts) {

                $lastStockholderId = null;

                foreach ($accounts as $account) {

                    $lastUserId++;

                    $accountNo = trim($account['accountNo']);
                    $userEmail = $account['role'] === 'stockholder'
                        ? (trim($account['email']) === "" ? null : trim(strtolower($account['email'])))
                        : (trim($account['corpRepEmail']) === "" ? null : trim(strtolower($account['corpRepEmail'])));

                    $insertUser[] = array(
                        'id' => $lastUserId,
                        'email' => $userEmail,
                        'role' => $account['role'],
                        'createdBy' => 1,
                        'createdAt' => $now
                    );


                    if ($account['role'] === 'stockholder') {

                        $lastStockholderId = $lastUserId;

                        $insertStockholder[] = array(
                            'stockholderId' => $lastStockholderId,
                            'accountNo' => $accountNo,
                            'stockholder' => trim($account['stockholder']),
                            'accountType' => $account['accountType'],
                            'voteInPerson' => $account['voteInPerson'],
                            'userId' => $lastUserId,
                            'createdBy' => 1,
                            'createdAt' => $now
                        );
                    }


                    if ($account['role'] === 'corp-rep') {

                        if ($lastStockholderId === null) {
                            Log::error("The stockholder ID is null for the account number: $accountNo. This should not happen.");
                            throw new ValidationErrorException("The stockholder ID is null for the account number: $accountNo. This should not happen.");
                        }


                        $corpRep = trim($account['corpRep']) === "" ? null : trim($account['corpRep']);

                        $insertStockholderAccount[] = array(

                            'accountKey' => $accountNo . '-' . $account['suffix'],
                            'corpRep' => $corpRep,
                            'suffix' => $account['suffix'],
                            'userId' => $lastUserId,
                            'stockholderId' => $lastStockholderId,
                            'createdBy' => 1,
                            'createdAt' => $now
                        );

                        if ($userEmail !== null) {
                            $groupByCorpRepEmail[$userEmail][$corpRep] = $accountNo;
                        }
                    }


                    $tempCombEmail = trim($account['combinedEmail']) === "" ? null : trim(strtolower($account['combinedEmail']));

                    if ($tempCombEmail !== null) {

                        $this->groupByCombinedEmail[$tempCombEmail][$account['accountNo']] = $account['accountNo'];
                    }
                }
            }


            foreach ($this->groupByCombinedEmail as $combinedEmailKey => $value) {

                if (count($value) > 1) {

                    Log::error("The email address '$combinedEmailKey' cannot be used in two different accounts. Import aborted.");

                    throw new ValidationErrorException("The email address '$combinedEmailKey' cannot be used in two different accounts. Import aborted.");
                }
            }

            foreach ($groupByCorpRepEmail as $combinedEmailKey => $value) {

                if (count($value) > 1) {

                    Log::error("The email address '$combinedEmailKey' cannot be used in two different corporate accounts. Import aborted.");

                    throw new ValidationErrorException("The email address '$combinedEmailKey' cannot be used in two different corporate accounts. Import aborted.");
                }
            }


            DB::beginTransaction();
            User::insert($insertUser);
            Stockholder::insert($insertStockholder);
            StockholderAccount::insert($insertStockholderAccount);

            DB::commit();
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {


            Log::error($e);

            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    private function validateImportRecordByRow($member, $index)
    {

        $validationRules =  [

            'stockholder'       => 'required|string|max:100',
            'accountNo'         => 'required|string|between:4,4',
            'suffix'            => 'required|integer|min:1|max:100',
            'accountType'       => 'required|in:indv,corp',
            'email'             => 'required|email|max:100',
            'voteInPerson'      => 'required|in:stockholder,corp-rep',
            'authSignatory'     => 'nullable|string|max:100',
            'corpRep'           => 'nullable|required_with:corpRepEmail|string|max:100',
            'corpRepEmail'      => 'nullable|required_with:corpRep|email',
            'isDelinquent'      => 'required|in:no,yes',

            'corp_rep.required_with' => 'The corporate representative name is required when corporate representative email is present.',
            'corp_rep_email.required_with' => 'The corporate representative email is required when corporate representative name is present.'

        ];


        $form = array_combine(['stockholder', 'accountNo', 'suffix', 'accountType', 'email', 'voteInPerson', 'authSignatory', 'corpRep', 'corpRepEmail', 'proxyFormNo', 'proxyDownload', 'spaDownload', 'isDelinquent'], $member);

        $request = new Request($form);

        $validator = Validator::make($request->all(), $validationRules);


        if ($validator->fails()) {
            Log::error("Validation failed for row $index: " . $validator->errors()->first(), [
                'row' => $index,
                'errors' => $validator->errors()
            ]);

            throw new ValidationErrorException($validator->errors()->first() . " Row: $index");
        }




        // Log::debug(gettype($request->input('corpRep')) . $request->input('corpRep'));
        // corporate representative is for corporate account only
        if ($request->input('accountType') === 'indv') {


            $corpRep = trim($request->input('corpRep')) === "" ? null : trim(strtolower($request->input('corpRep')));
            $corpRepEmail = trim($request->input('corpRepEmail')) === "" ? null : strtolower(trim($request->input('corpRepEmail')));

            if ($corpRep !== null || $corpRepEmail !== null) {
                Log::info("Corporate representative details found for individual account: $corpRep, $corpRepEmail");

                throw new ValidationErrorException('The corporate representative details are for corporate type of account only.');
            }

            if ($request->input('voteInPerson') === 'corp-rep') {
                Log::error("Vote in person field must be set to stockholder when the account type is indv. Account Number: " . $request->input('accountNo'));
                throw new ValidationErrorException('Vote in person field must be set to stockholder when the account type is indv. Account Number: ' . $request->input('accountNo'));
            }
        }


        //stockholder email and corporate email cannot be the same
        if ($request->input('accountType') === 'corp') {

            $stockholderEmail = trim($request->input('email')) === "" ? null : strtolower(trim($request->input('email')));
            $corpRepEmail = trim($request->input('corpRepEmail')) === "" ? null : strtolower(trim($request->input('corpRepEmail')));


            if ($stockholderEmail !== null || $corpRepEmail !== null) {


                if ($stockholderEmail === $corpRepEmail) {
                    Log::error("Stockholder email and corporate representative email cannot be the same. Email: $stockholderEmail");
                    throw new ValidationErrorException('The stockholder email and corp rep email cannot be the same. Email: ' . $request->input('email'));
                }
            }
        }


        return $request;
    }

    private function checkForExistingStockholders(): array
    {

        $stockholderAccountsArr = Stockholder::with('stockholderAccounts', 'stockholderAccounts.user:id,email', 'user:id,email')->get()->toArray();

        $existingStockholders = [];

        foreach ($stockholderAccountsArr as $stockholderAccount) {

            $stockholderUser = $stockholderAccount['user'];
            $accountNo = $stockholderAccount['accountNo'];
            $stockholderUserEmail = $stockholderUser['email'];

            if ($stockholderUserEmail === null) {

                Log::error("Found stockholder account without email on the existing stockholder. Account No: $accountNo");

                throw new ValidationErrorException("Stockholder email is required.");
            }

            $this->groupByCombinedEmail[$stockholderUserEmail][$accountNo] = $accountNo;

            foreach ($stockholderAccount['stockholder_accounts'] as $account) {

                $accountUser = $account['user'];

                $accountUserEmail = $accountUser['email'];
                if ($accountUserEmail !== null) {

                    $this->groupByCombinedEmail[$accountUserEmail][$accountNo] = $accountNo;
                }
            }

            $existingStockholders[$accountNo] = $stockholderAccount;  //change the index to accountNo


        }

        return $existingStockholders;
    }

    private function groupByAccountNo($dataFromExcel): array
    {
        $groupByAccountNo = [];
        $tempCounter = 1;
        foreach ($dataFromExcel as $member) {

            $tempCounter++;
            $request = $this->validateImportRecordByRow($member, $tempCounter);
            $accountNo = $request->input('accountNo');
            $groupByAccountNo[$accountNo][] = array_merge(

                $request->all(),

                [
                    'accountKey' => $accountNo . '-' . (int) $request->input('suffix'),
                    'role' => 'corp-rep'
                ]

            );
        }



        return $groupByAccountNo;
    }

    private function validateAndCompareExistingAndImportFile($existingStockholders, $keyAccountNo, $firstAccount)
    {

        $firstStockholder = $firstAccount['stockholder'];
        $firstAccountType = $firstAccount['accountType'];
        $firstVoteInPeson = $firstAccount['voteInPerson'];
        $firstEmail       = trim($firstAccount['email']) === "" ? null : trim(strtolower($firstAccount['email']));

        $existingAccount = $existingStockholders[$keyAccountNo];


        if (strtolower($existingAccount['stockholder']) !== strtolower($firstStockholder)) {
            Log::error("Each stockholder must have a unique stockholder name. Account Number: $keyAccountNo. Stockholder must be " . $existingAccount['stockholder']);
            throw new ValidationErrorException("Each stockholder must have a unique stockholder name. Account Number: $keyAccountNo. Stockholder must be " . $existingAccount['stockholder']);
        }

        if ($existingAccount['accountType'] !== $firstAccountType) {
            Log::error("Each stockholder must have a unique account type. Account Number: $keyAccountNo");
            throw new ValidationErrorException("Each stockholder must have a unique account type. Account Number: $keyAccountNo");
        }

        if ($existingAccount['voteInPerson'] !== $firstVoteInPeson) {
            Log::error("Each account number must have a unique vote in person field. Account Number: $keyAccountNo");
            throw new ValidationErrorException("Each account number must have a unique vote in person field. Account Number: $keyAccountNo");
        }


        if (strtolower($existingAccount['user']['email']) !== $firstEmail) {
            Log::error("Each stockholder number must have a unique email address for stockholder. Account Number: $keyAccountNo");
            throw new ValidationErrorException("Each stockholder number must have a unique email address for stockholder. Account Number: $keyAccountNo");
        }
    }

    private function validateUniqueness($account, $firstAccount, $keyAccountNo)
    {
        $expectedStockholder = strtolower($firstAccount['stockholder']);
        $expectedAccountType = $firstAccount['accountType'];
        $expectedVoteInPerson = $firstAccount['voteInPerson'];
        $expectedEmail = trim($firstAccount['email']) === "" ? null : strtolower(trim($firstAccount['email']));

        if (strtolower($account['stockholder']) !== $expectedStockholder) {
            Log::error("Each account number must have a unique stockholder name. Account Number: $keyAccountNo. Expected stockholder: $expectedStockholder");
            throw new ValidationErrorException("Each account number must have a unique stockholder name. Account Number: $keyAccountNo. Expected stockholder: $expectedStockholder");
        }

        if ($account['accountType'] !== $expectedAccountType) {
            Log::error("Each account number must have a unique account type. Account Number: $keyAccountNo. Expected account type: $expectedAccountType");
            throw new ValidationErrorException("Each account number must have a unique account type. Account Number: $keyAccountNo. Expected account type: $expectedAccountType");
        }

        if ($account['voteInPerson'] !== $expectedVoteInPerson) {
            Log::error("Each account number must have a unique 'vote in person' field. Account Number: $keyAccountNo. Expected value: $expectedVoteInPerson");
            throw new ValidationErrorException("Each account number must have a unique 'vote in person' field. Account Number: $keyAccountNo. Expected value: $expectedVoteInPerson");
        }

        if (strtolower($account['email']) !== $expectedEmail) {
            Log::error("Each stockholder number must have a unique email address for the stockholder. Account Number: $keyAccountNo. Expected email: $expectedEmail");
            throw new ValidationErrorException("Each stockholder number must have a unique email address for the stockholder. Account Number: $keyAccountNo. Expected email: $expectedEmail");
        }
    }
}
