<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EApp;
use App\Http\Requests\EditStockholderRequest;
use App\Http\Requests\UpdateStockholderRequest;
use App\Http\Requests\UpdateAuthSignatoryRequest;
use App\Http\Requests\UpdateCorporateRepRequest;
use App\Models\NonMemberAccount;
use App\Models\Stockholder;
use App\Models\StockholderAccount;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class StockholderService
{


    public function index()
    {
        try {
            return view('admin.members', ['title' => 'Stockholders']);
        } catch (Exception $e) {

            AppHelper::logServerError("Error loading stockholders page: " . $e->getMessage(), $e);
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }
    // Define your service methods here
    public function store(Request $request)
    {
        try {
            Log::info('Storing stockholder', ['account_no' => $request->account_number]);
            DB::beginTransaction();
            if ($request->account_number == "0000") {
                return response()->json([
                    'message' => 'Account number 0000 is reserved exclusively for the General Manager\'s account.'
                ], 400);
            }
            $stockholderInfo = Stockholder::with('user')->where('accountNo', $request->account_number)->first();
            if ($stockholderInfo !== null) {
                $addStock = $this->handleAdditionalStock($request, $stockholderInfo);
                DB::commit();
                return $addStock;
            }
            $addUserAndStock = $this->handleNewAccount($request);
            DB::commit();
            return $addUserAndStock;
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            Log::info('Validation error in stockholder creation', [
                'error' => $e->getMessage(),
                'request_data' => $request->only(['account_number', 'stockholder', 'account_type'])
            ]);
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {
            DB::rollBack();
            UtilityService::logServerError($request, $e,  "Error occurred while storing stockholder.",  ["test" => "value"]);
            return response()->json([], 500);
        }
    }
    private function handleNewAccount(Request $request)
    {
        $email = strtolower($request->email);
        $corpRepEmail = strtolower($request->corp_rep_email);
        $this->storeBusinessValidation($request);
        $userForStockholder = $this->createUserForStockholder($email, Auth::id());
        $stockholderInfo = $this->createStockholder($userForStockholder, $request);
        $userForStockholderAccount = $this->createUserForStockholderAccount($corpRepEmail);
        $stockhholderAccount = $this->createStockholderAccount($stockholderInfo, $userForStockholderAccount->id, $request);
        $this->logStoreNewStockholderActivity($userForStockholder, $stockholderInfo, $stockhholderAccount, $userForStockholderAccount, $request);
        Log::info('Stockholder and account created successfully', [
            'stockholder_id' => $stockholderInfo->id,
            'account_id' => $userForStockholderAccount->id
        ]);
        return response()->json(['message' => 'The stockholder has been added successfully.'], 200);
    }

    private function logStoreNewStockholderActivity($userForStockholder, $stockholderInfo, $stockhholderAccount, $userForStockholderAccount, $request)
    {
        // Log creation of initial stock account
        $remarks = sprintf(
            'Created initial stock account for <strong style="color: #2563eb;">"%s"</strong> (Account Key: <span style="color: #dc2626; font-weight: bold;">%s</span>',
            $stockholderInfo->stockholder,
            $stockhholderAccount->accountKey
        );
        if (!empty($request->corp_rep)) {
            $remarks .= sprintf(', Corporate Representative: <strong style="color: #ea580c;">%s</strong>', $request->corp_rep);
        }
        $remarks .= ')';
        ActivityController::log([
            'activityCode' => '00103',
            'userId' => $userForStockholderAccount->id,
            'remarks' => $remarks,
        ]);
        // Log creation of new stockholder account
        ActivityController::log([
            'activityCode' => '00002',
            'userId' => $userForStockholder->id,
            'remarks' => sprintf(
                'Created new stockholder account for <strong style="color: #2563eb;">"%s"</strong> (Account No. <span style="color: #059669; font-weight: bold;">%s</span>) | Email: <em style="color: #7c3aed;">%s</em>',
                $stockholderInfo->stockholder,
                $stockholderInfo->accountNo,
                $userForStockholder->email
            ),
        ]);
    }


    private function createStockholderAccount($stockholderInfo, $stockholderAccountUserId, $request): StockholderAccount
    {

        $accountType    = strtolower($request->account_type);

        return $stockholderInfo->stockholderAccounts()->create([
            'userId'            => $stockholderAccountUserId,
            'accountKey'         => $request->account_number . '-' . $request->suffix,
            'suffix'             => $request->suffix,
            'corpRep'            => $accountType === 'corp' ? $request->corp_rep : null,
            'authSignatory'      => $accountType === 'corp' ? $request->auth_signatory : null,
            'isDelinquent'       => $request->delinquent,
            'createdBy'          => Auth::id()
        ]);
    }
    private function createUserForStockholderAccount($corpRepEmail): User
    {

        return User::create(['role' => 'corp-rep', 'email' => $corpRepEmail, 'createdBy' => Auth::id()]);
    }

    private function createUserForStockholder($email, $uId)
    {

        return User::create(['role' => 'stockholder', 'email' => $email, 'createdBy' => $uId]);
    }

    private function createStockholder($userForStockholder, $request): Stockholder
    {
        $accountType = strtolower($request->account_type);
        return $userForStockholder->stockholder()->create([
            'accountNo' => $request->account_number,
            'stockholder' => $request->stockholder,
            'accountType' => $accountType,
            'voteInPerson' => $accountType === 'indv' ? 'stockholder' : strtolower($request->vote_in_person),
            'createdBy' => Auth::user()->id,
        ]);
    }



    private function storeBusinessValidation(Request $request): void
    {


        Log::info('Validating stockholder account business rules');

        // Check if account number exists in nonmember_accounts
        if (NonMemberAccount::where('nonmemberAccountNo', $request->account_number)->exists()) {
            Log::info('Account number already exists in nonmember_accounts: ' . $request->account_number);
            throw new ValidationErrorException('Account number already exists in nonmember accounts.');
        }

        // Check if account number exists in stockholders
        if (Stockholder::where('accountNo', $request->account_number)->exists()) {
            Log::info('Account number already exists in stockholders: ' . $request->account_number);
            throw new ValidationErrorException('Account number already exists in stockholders.');
        }


        $email = strtolower($request->email);
        $corpRepEmail = strtolower($request->corp_rep_email);


        if (User::where('email', $email)->exists()) {
            Log::info('Email already exists: ' . $email);
            throw new ValidationErrorException('Email already exists.');
        }


        if (!empty($corpRepEmail)) {


            if ($email === $corpRepEmail) {

                Log::info('The stockholder\'s email and the corporate representative\'s email cannot be the same.');

                throw new ValidationErrorException('The stockholder\'s email and the corporate representative\'s email cannot be the same.');
            }
            if (User::where('email', $corpRepEmail)->exists()) {

                Log::info('Corporate email already exists: ' . $corpRepEmail);
                throw new ValidationErrorException('Corporate email already exists.');
            }
        }

        Log::info('Stockholder account business rules validated successfully.');
    }


    private function handleAdditionalStock(Request $request, Stockholder $stockholderInfo)
    {

        $this->checkIfSuffixExists($request, $stockholderInfo);

        $user = $this->createUserForAdditionalStockholderAccount($stockholderInfo, $request->corp_rep_email);
        $this->createAdditionalStockholderAccount($stockholderInfo, $user, $request);


        ActivityController::log(['activityCode' => '00098', 'remarks' => $stockholderInfo->accountNo . '-' . $request->suffix, 'userId' => $user->id]);

        return response()->json(['message' => 'A new stock has been added successfully.'], 200);
    }

    private function createAdditionalStockholderAccount(Stockholder $stockholderInfo, User $user, Request $request)
    {

        return $user->stockholderAccount()->create([
            'accountKey' => $stockholderInfo->accountNo . '-' . $request->suffix,
            'suffix' => $request->suffix,
            'corpRep' => $stockholderInfo->accountType === 'corp' ? $request->corp_rep : null,
            'authSignatory' => $stockholderInfo->accountType === 'corp' ? $request->auth_signatory : null,
            'isDelinquent' => $request->delinquent,
            'userId' => $user->id,
            'stockholderId' => $stockholderInfo->stockholderId,
            'createdBy' => Auth::id()
        ]);
    }

    private function createUserForAdditionalStockholderAccount(Stockholder $stockholderInfo, ?string $corpRepEmail): User
    {
        $corpRepEmail = $corpRepEmail === null ? null : strtolower($corpRepEmail);
        return User::create([
            'email' => $stockholderInfo->accountType === 'corp' ? $corpRepEmail : null,
            'role' => 'corp-rep',
            'createdBy' => Auth::id()
        ]);
    }






    private function checkIfSuffixExists(Request $request, Stockholder $stockholderInfo)
    {

        $suffixExists = $stockholderInfo->stockholderAccounts()->where('suffix', $request->suffix)->exists();

        if ($suffixExists === true) {

            Log::info('Suffix already exists for account number: ' . $request->account_number . ' with suffix: ' . $request->suffix);

            throw new ValidationErrorException('Suffix already exists.');
        }
    }

    public function update(UpdateStockholderRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::with('stockholder', 'stockholderAccount.stockholder')
                ->where('id', $request->id)
                ->firstOrFail();

            $result = $this->processUpdateByUserRole($request, $user);

            DB::commit();

            return $result;
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            Log::warning('Validation error during stockholder update', [
                'error' => $e->getMessage(),
                'user_id' => $request->id,
                'request_data' => $request->only(['id', 'email', 'stockholder'])
            ]);

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {
            DB::rollBack();
            AppHelper::logServerError("Error occurred while updating stockholder.", $e, [
                'user_id' => $request->id,
                'user_role' => $user->role ?? 'unknown'
            ]);
            return response()->json([], 500);
        }
    }

    /**
     * Route update request to appropriate processor based on user role and account type.
     * 
     * @param UpdateStockholderRequest $request
     * @param User $user
     * @return array|\Illuminate\Http\JsonResponse
     * @throws ValidationErrorException
     */
    private function processUpdateByUserRole(Request $request, User $user)
    {
        if ($user->role === 'stockholder') {
            return $this->processStockholderUpdate($request, $user);
        }

        return $this->processStockUpdate($request, $user);
    }






    private function validateUpdateDelinquentStatusPermission(StockholderAccount $stockholderAccount, int $isDelinquent)
    {

        if ($stockholderAccount->isDelinquent !== $isDelinquent) {

            $action = $isDelinquent === 1 ? 'mark stock delinquent' : 'mark stock active';
            if (Auth::user()->cannot($action)) {
                $status = $isDelinquent === 1 ? 'delinquent' : 'active';
                throw new ValidationErrorException("You do not have permission to mark this stock as {$status}.");
            }
        }
    }





    private function validateStockCorporateUpdate(Request $request): void
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id'                => 'required|numeric|integer',
                'delinquent'        => 'nullable|in:0,1',
            ]
        );

        if ($validator->fails()) {
            $err = EApp::obj_to_array($validator->errors());
            throw new ValidationErrorException($err[array_key_first($err)][0], array_key_first($err));
        }
    }

    /**
     * Process stock delinquency status update for corporate representative accounts.
     *
     * @param Request $request
     * @param User $corporateRepUser User with stockholder account (corp-rep role)
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationErrorException
     */
    private function processStockUpdate(Request $request, User $corporateRepUser): \Illuminate\Http\JsonResponse
    {
        $this->validateStockCorporateUpdate($request);

        $userId = $corporateRepUser->id;
        $newIsDelinquent = (int) $request->input('delinquent');
        $stockholderAccount = $corporateRepUser->stockholderAccount;
        $currentIsDelinquent = (int) $stockholderAccount->isDelinquent;

        if ($currentIsDelinquent === $newIsDelinquent) {
            return response()->json(['message' => 'No changes were made.'], 400);
        }

        $this->validateUpdateDelinquentStatusPermission($stockholderAccount, $newIsDelinquent);

        $changeTracker = [
            'status' => [
                'old' => $this->getDelinquentStatusText($currentIsDelinquent),
                'new' => $this->getDelinquentStatusText($newIsDelinquent),
            ],
        ];

        $stockholderAccount->isDelinquent = $newIsDelinquent;
        $stockholderAccount->save();

        UserService::logoutUpdatedUser((string) $userId);

        ActivityController::log([
            'activityCode' => '00029',
            'accountNo' => $stockholderAccount->accountKey,
            'userId' => $userId,
            'email' => $stockholderAccount->stockholder->accountType === 'corp'
                ?  $corporateRepUser->email
                : $stockholderAccount->stockholder->user->email,
            'data' => json_encode($changeTracker),
        ]);

        return response()->json(['message' => 'Stock successfully updated to ' . $this->getDelinquentStatusText($newIsDelinquent) . '.'], 200);
    }

    /**
     * Convert delinquent status integer to text representation.
     *
     * @param int $status 0 (active) or 1 (delinquent)
     * @return string 'active' or 'delinquent'
     */
    private function getDelinquentStatusText(int $status): string
    {
        return $status === 1 ? 'delinquent' : 'active';
    }





    private function processStockholderUpdate(Request $request, User $stockholderUser): \Illuminate\Http\JsonResponse
    {

        $userId = $stockholderUser->id;
        $newStockholderName = AppHelper::normalizeString($request->input('stockholder'));
        $isCorporateAccount = $stockholderUser->stockholder->accountType === 'corp';

        $this->validateStockholderUpdateBusinessRules($stockholderUser, $request);

        $validatedData = [
            'stockholder' => $newStockholderName,
            'voteInPerson' => $isCorporateAccount
                ? AppHelper::normalizeEmail($request->input('vote_in_person'))
                : 'stockholder',
        ];

        $trackedChanges = $this->trackStockholderChanges($stockholderUser, $validatedData);

        if (empty($trackedChanges)) {
            return response()->json(['message' => 'No changes detected.'], 400);
        }

        // For individual accounts, validate proxy email usage before name change
        if (!$isCorporateAccount && isset($trackedChanges['stockholder'])) {
            UserService::validateEmailConflicts($userId, $stockholderUser->email, $newStockholderName);
            ProxyService::validateProxyEmailUsage($stockholderUser->email);
        }

        $this->updateStockholderInfo($stockholderUser, $validatedData);
        UserService::logoutUpdatedUser((string) $userId);
        ActivityController::log([
            'activityCode' => '00029',
            'accountNo' => $stockholderUser->stockholder->accountNo,
            'userId' => $userId,
            'email' => $stockholderUser->email,
            'data' => json_encode($trackedChanges)
        ]);

        return response()->json(['message' => 'Stockholder updated successfully'], 200);
    }


    private function updateStockholderInfo(User $stockholderUser, array $validatedData): void
    {
        $stockholderInfo = $stockholderUser->stockholder;

        $stockholderInfo->stockholder = $validatedData['stockholder'];
        $stockholderInfo->voteInPerson = $validatedData['voteInPerson'];
        $stockholderInfo->save();
    }



    /**
     * Normalize email to lowercase.
     * 
     * @param string|null $email Email to normalize
     * @return string|null Normalized email or null
     */
    private function normalizeEmail(?string $email): ?string
    {
        return $email === null ? null : strtolower($email);
    }



    /**
     * Persist email change to database.
     * 
     * @param User $user User to update
     * @param string|null $newEmail New email address
     */
    private function persistEmailChange(User $user, ?string $newEmail): void
    {
        $user->email = $newEmail;
        $user->save();
    }








    private function validateStockholderUpdateBusinessRules(User $userForStockholder, Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'id'                => 'required|integer',
                'stockholder'       => 'required|string|max:100',
                'vote_in_person' => [
                    Rule::requiredIf(
                        $userForStockholder->stockholder->accountType === 'corp'
                    ),
                    'in:stockholder,corp-rep'
                ],
            ]
        );

        if ($validator->fails()) {
            $err = EApp::obj_to_array($validator->errors());
            throw new ValidationErrorException($err[array_key_first($err)][0]);
        }
    }

    private function trackStockholderChanges(User $stockholderUser, array $validatedData)
    {

        $stockholderInfo = $stockholderUser->stockholder;

        $changes = [];


        // Track non-member changes
        $fieldsToTrack = ['stockholder', 'voteInPerson'];

        //map field names
        $fieldMap = [
            'stockholder' => 'stockholder',
            'voteInPerson' => 'Online Voter',
        ];

        foreach ($fieldsToTrack as $field) {

            if ($stockholderInfo->$field !== $validatedData[$field]) {
                $changes[$fieldMap[$field]] = [
                    'old' => $stockholderInfo->$field === null ? '(empty)' : $stockholderInfo->$field,
                    'new' => $validatedData[$field] === null ? '(empty)' : $validatedData[$field]
                ];
            }
        }

        return $changes;
    }


    public function loadAssigneeOptions()
    {
        try {

            $onlineAccountService = new OnlineAccountService();

            $onlineAccounts = $onlineAccountService->getOnlineAccountsQuery()->get();
            $accountsByEmailAndName = $onlineAccountService->groupAccountsByEmailAndName($onlineAccounts);

            // Extract unique email and its first account name
            $uniqueEmailsAndNames = $onlineAccountService->extractUniqueEmailsWithNames($accountsByEmailAndName);

            //sort this by array value $uniqueEmailsAndNames
            uasort($uniqueEmailsAndNames, function ($a, $b) {
                return strcmp($a, $b);
            });

            return response()->json([
                'success' => true,
                'onlineAccounts' => $uniqueEmailsAndNames
            ], 200);
        } catch (Exception $e) {
            AppHelper::logServerError("Error loading option assignees: " . $e->getMessage(), $e);
            return response()->json(['success' => false, 'message' => EApp::SERVER_ERROR], 500);
        }
    }

    public function editAuthSignatory(int $userId)
    {

        if (Auth::user()->cannot('edit authorized signatory') && Auth::user()->role !== 'superadmin') {
            return response()->json(['message' => 'You do not have permission to edit the authorized signatory.'], 403);
        }

        $user = User::with('stockholder')->where('id', $userId)->whereHas('stockholder')->firstOrFail();

        return response()->json([
            'success' => true,
            'authorizedSignatory' => $user
        ], 200);
    }


    public function editCorpRepresentative(int $userId)
    {

        if (Auth::user()->cannot('edit corporate representative') && Auth::user()->role !== 'superadmin') {
            return response()->json(['message' => 'You do not have permission to edit the corporate representative.'], 403);
        }
        $user = User::with('stockholderAccount.stockholder')
            ->where('id', $userId)
            ->whereHas('stockholderAccount.stockholder', function ($query) {
                $query->where('accountType', 'corp');
            })
            ->where('role', 'corp-rep')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'corpRepresentative' => $user
        ], 200);
    }

    public function updateAuthSignatory(UpdateAuthSignatoryRequest $request, int $userId)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $authSignatoryEmail = $validatedData['auth_signatory_email'];
            $authSignatory = $validatedData['auth_signatory'];

            // Load user with relationships
            $stockholderUser = User::with('stockholder', 'stockholderAccount')
                ->where('id', $userId)
                ->whereHas('stockholder', function ($query) {
                    $query->where('accountType', 'corp');
                })
                ->firstOrFail();

            // Validate email conflicts and proxy usage
            UserService::validateEmailConflicts($userId, $authSignatoryEmail, $authSignatory);
            ProxyService::validateProxyEmailUsage($stockholderUser->email);

            // Track and apply changes
            $stockholder = $stockholderUser->stockholder;
            $changeTracker = $this->trackAuthSignatoryChanges($stockholderUser, $stockholder, $authSignatoryEmail, $authSignatory);

            if (empty($changeTracker)) {
                DB::rollBack();
                return response()->json(['message' => 'No changes were made.'], 400);
            }

            // Update user and stockholder
            $originalEmail = $stockholderUser->email;
            if (isset($changeTracker['email'])) {
                $stockholderUser->email = $authSignatoryEmail;
                $stockholderUser->save();
            }

            if (isset($changeTracker['authorized_signatory'])) {
                $stockholder->authorizedSignatory = $authSignatory;
                $stockholder->save();
            }

            // Cleanup and logging
            UserService::logoutUpdatedUser((string) $userId);

            ActivityController::log([
                'activityCode' => '00029',
                'accountNo' => $stockholder->accountNo,
                'email' => $originalEmail,
                'userId' => $userId,
                'data' => json_encode($changeTracker)
            ]);

            DB::commit();


            return response()->json(['message' => 'Authorized signatory updated successfully'], 200);
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {
            DB::rollBack();
            AppHelper::logServerError("Error updating authorized signatory: " . $e->getMessage(), $e, ['user_id' => $userId]);
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }



    /**
     * Track changes to authorized signatory and email.
     * 
     * @param User $user User to check against
     * @param Stockholder $stockholder Stockholder record
     * @param string $newEmail New email address
     * @param string $newSignatory New signatory name
     * @return array Array of tracked changes
     */
    private function trackAuthSignatoryChanges(User $user, Stockholder $stockholder, ?string $newEmail, ?string $newSignatory): array
    {
        $changeTracker = [];

        // Check email changes
        if (!AppHelper::compareStrings($user->email, $newEmail)) {
            $changeTracker['email'] = [
                'old' => $user->email ?? '(empty)',
                'new' => $newEmail ?? '(empty)'
            ];
        }

        // Check signatory name changes
        if (!AppHelper::compareStrings($stockholder->authorizedSignatory, $newSignatory)) {
            $changeTracker['authorized_signatory'] = [
                'old' => $stockholder->authorizedSignatory ?? '(empty)',
                'new' => $newSignatory ?? '(empty)'
            ];
        }

        return $changeTracker;
    }


    public function updateCorporateRep(UpdateCorporateRepRequest $request, int $userId)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $corporateRepEmail = $validatedData['corp_rep_email'];
            $corporateRep = $validatedData['corp_rep'];

            // Load user with relationships
            $corporateRepUser = User::with('stockholderAccount.stockholder')
                ->whereHas('stockholderAccount.stockholder', function ($query) {
                    $query->where('accountType', 'corp');
                })
                ->where('role', 'corp-rep')
                ->where('id', $userId)
                ->whereHas('stockholderAccount')
                ->firstOrFail();


            // Validate email conflicts and proxy usage
            UserService::validateEmailConflicts($userId, $corporateRepEmail, $corporateRep);
            ProxyService::validateProxyEmailUsage($corporateRepUser->email);



            // Track and apply changes
            $stockholderAccount = $corporateRepUser->stockholderAccount;
            $changeTracker = $this->trackCorporateRepChanges($corporateRepUser, $stockholderAccount, $corporateRepEmail, $corporateRep);

            if (empty($changeTracker)) {
                DB::rollBack();
                return response()->json(['message' => 'No changes were made.'], 400);
            }

            // Update user and stockholder account
            $originalEmail = $corporateRepUser->email;
            if (isset($changeTracker['email'])) {
                $corporateRepUser->email = $corporateRepEmail;
                $corporateRepUser->save();
            }

            if (isset($changeTracker['corporate_rep'])) {
                $stockholderAccount->corpRep = $corporateRep;
                $stockholderAccount->save();
            }

            // Cleanup and logging
            UserService::logoutUpdatedUser((string) $userId);

            ActivityController::log([
                'activityCode' => '00145',
                'accountNo' => $stockholderAccount->stockholder->accountNo,
                'email' => $originalEmail,
                'userId' => $userId,
                'data' => json_encode($changeTracker)
            ]);

            DB::commit();

            return response()->json(['message' => 'Corporate representative updated successfully'], 200);
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {
            DB::rollBack();
            AppHelper::logServerError("Error updating corporate representative: " . $e->getMessage(), $e, ['user_id' => $userId]);
            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }



    /**
     * Track changes to corporate representative and email.
     * 
     * @param User $user User to check against
     * @param StockholderAccount $stockholderAccount Stockholder account record
     * @param string $newEmail New email address
     * @param string $newCorpRep New corporate representative name
     * @return array Array of tracked changes
     */
    private function trackCorporateRepChanges(User $user, StockholderAccount $stockholderAccount, ?string $newEmail, ?string $newCorpRep): array
    {
        $changeTracker = [];

        // Check email changes
        if (!AppHelper::compareStrings($user->email, $newEmail)) {
            $changeTracker['email'] = [
                'old' => $user->email ?? '(empty)',
                'new' => $newEmail ?? '(empty)'
            ];
        }

        // Check corporate representative name changes
        if (!AppHelper::compareStrings($stockholderAccount->corpRep, $newCorpRep)) {
            $changeTracker['corporate_rep'] = [
                'old' => $stockholderAccount->corpRep ?? '(empty)',
                'new' => $newCorpRep ?? '(empty)'
            ];
        }

        return $changeTracker;
    }
}
