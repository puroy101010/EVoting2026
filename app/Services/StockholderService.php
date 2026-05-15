<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EApp;
use App\Http\Requests\EditStockholderRequest;
use App\Models\NonMemberAccount;
use App\Models\Stockholder;
use App\Models\StockholderAccount;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpParser\Lexer\TokenEmulator\VoidCastEmulator;

class StockholderService
{
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

    public function update(EditStockholderRequest $request)
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
     * @param EditStockholderRequest $request
     * @param User $user
     * @return array|\Illuminate\Http\JsonResponse
     * @throws ValidationErrorException
     */
    private function processUpdateByUserRole(EditStockholderRequest $request, User $user)
    {
        if ($user->role === 'stockholder') {
            return $this->processStockholderUpdate($request, $user);
        }

        // For corp-rep role, check if managing corporate or individual account
        if ($user->stockholderAccount->stockholder->accountType === 'corp') {
            return $this->processStockUpdate($request, $user);
        }

        return $this->processStockholderAccountIndvRoleUpdate($request);
    }




    private function processStockholderAccountIndvRoleUpdate(Request $request)
    {

        $this->validateStockholderAccountIndvUpdate($request);


        $id             = $request->input('id');
        $isDelinquent   = (int) $request->input('delinquent');



        $userForStockholdeAccount = User::with('stockholder')->findOrFail($id);
        $stockholderAccount = $userForStockholdeAccount->stockholderAccount;
        $changeTracker = $this->trackStockholderAccountIndvChanges($stockholderAccount, $request);
        if (empty($changeTracker)) {

            return response()->json(['message' => 'No changes were made.'], 400);
        }


        $this->validateUpdateDelinquentStatusPermission($stockholderAccount, $isDelinquent);

        $stockholderAccount->isDelinquent = $isDelinquent;

        $stockholderAccount->save();

        $this->logoutUpdatedUser($id);

        ActivityController::log(['activityCode' => '00029', 'accountNo' => $stockholderAccount->accountKey, 'userId' => $id, 'data' => json_encode($changeTracker)]);

        return response()->json(['message' => 'Account updated successfully'], 200);
    }

    private function validateUpdateDelinquentStatusPermission($stockholderAccount, $isDelinquent)
    {

        if ($stockholderAccount->isDelinquent !== $isDelinquent) {

            $action = $isDelinquent === 1 ? 'mark stock delinquent' : 'mark stock active';
            if (Auth::user()->cannot($action)) {
                $status = $isDelinquent === 1 ? 'delinquent' : 'active';
                Log::warning("Stockholder: Unauthorized {$status} status update attempt", [
                    'account_key' => $stockholderAccount->accountKey,
                    'old_status' => $stockholderAccount->isDelinquent,
                    'new_status' => $isDelinquent
                ]);
                throw new ValidationErrorException("You do not have permission to mark this stock as {$status}.");
            }
        }
    }

    private function trackStockholderAccountIndvChanges($stockholderAccount, $request)
    {

        $changeTracker = [];

        if ($stockholderAccount->isDelinquent === (int)$request->input('delinquent')) {

            Log::info('No changes in delinquency status for account number');
            return response()->json(['message' => 'No changes were made.'], 400);
        }

        $isDelinquentMap = ['1' => 'delinquent', '0' => 'active'];

        $changeTracker['status'] = [
            'old' => $isDelinquentMap[$stockholderAccount->isDelinquent],
            'new' => $isDelinquentMap[$request->input('delinquent')]
        ];

        return $changeTracker;
    }

    private function validateStockholderAccountIndvUpdate(Request $request)
    {


        $validator = Validator::make(

            $request->all(),
            [
                'id'                => 'required|numeric|integer',
                'delinquent'        => 'required|in:0,1',
            ]
        );


        if ($validator->fails()) {

            $err = EApp::obj_to_array($validator->errors());

            throw new ValidationErrorException($err[array_key_first($err)][0], array_key_first($err));
        }
    }
    private function processStockUpdate(Request $request, User $userForCorpRep)
    {

        $isCorpAccount = $userForCorpRep->stockholderAccount->stockholder->accountType === 'corp';

        $this->validateStockCorporateUpdate($request);

        $userId = $request->input('id');
        $corpRep = $request->input('corp_rep');
        $corpRepEmail = $this->normalizeEmail($request->input('corp_rep_email'));
        $isDelinquent = (int) $request->input('delinquent');

        $authorizedSignatory = $isCorpAccount
            ? $request->input('corp_rep')
            : $request->input('stockholder');

        Log::debug('signatory', [
            'auth_signatory' => $request->input('auth_signatory'),
            'corp_rep' => $request->input('corp_rep'),
            'is_corp_account' => $isCorpAccount
        ]);

        $validatedData = [
            'email' => $corpRepEmail,
            'corpRep' => $corpRep,
            'isDelinquent' => (int) $isDelinquent,
            'authorizedSignatory' =>  $authorizedSignatory
        ];



        $this->validateEmailConflicts($userId, $corpRepEmail, $authorizedSignatory);

        $stockholderInfo = $userForCorpRep->stockholderAccount->stockholder;


        $changeTracker = $this->trackCorpRepChanges($userForCorpRep, $validatedData);


        if (empty($changeTracker)) {
            return response()->json(['message' => 'No changes were made.'], 400);
        }

        $this->validateUpdateCorpRepEmailPermission($userForCorpRep, $validatedData);


        $this->validateUpdateDelinquentStatusPermission($userForCorpRep->stockholderAccount, $isDelinquent);
        $stockholderAccount = $userForCorpRep->stockholderAccount;

        $userForCorpRep->email = $corpRepEmail;
        $stockholderAccount->corpRep        = $corpRep;
        $stockholderAccount->isDelinquent   = $isDelinquent;
        $stockholderAccount->authSignatory  = $authorizedSignatory;

        $userForCorpRep->save();
        $stockholderAccount->save();


        $this->logoutUpdatedUser($userId);
        ActivityController::log([
            'activityCode' => '00029',
            'accountNo' => $stockholderAccount->accountKey,
            'userId' => $userId,
            'data' => json_encode($changeTracker)
        ]);


        Log::info('Corporate representative account updated successfully', [
            'user_id' => $userId,
            'changes' => $changeTracker
        ]);

        return response()->json(['message' => 'Account updated successfully', 200]);
    }

    private function validateUpdateCorpRepEmailPermission(User $userForCorpRep, array $validatedData)
    {
        if ($userForCorpRep->email !== $validatedData['email']) {
            if (Auth::user()->cannot('edit corporate representative email')) {
                Log::warning("Stockholder: Unauthorized corporate email edit attempt", [
                    'user_id' => $userForCorpRep->id,
                    'old_email' => $userForCorpRep->email,
                    'new_email' => $validatedData['email']
                ]);
                throw new ValidationErrorException('You do not have permission to edit this corporate representative email.');
            }
        }
    }

    private function trackCorpRepChanges(User $userForCorpRep, array $validatedData)
    {


        $stockholderAccount = $userForCorpRep->stockholderAccount;


        $changes = [];

        // Track user changes
        if ($userForCorpRep->email !== $validatedData['email']) {
            $changes['email'] = [
                'old' => $userForCorpRep->email,
                'new' => $validatedData['email']
            ];
        }


        // Track non-member changes
        $fieldsToTrack = ['corpRep', 'authorizedSignatory'];

        $isDelinquentMap = ['1' => 'delinquent', '0' => 'active'];

        //map field names
        $fieldMap = [
            'corpRep' => 'corporate representative',
            'authorizedSignatory' => 'authorized signatory'
        ];

        if ($stockholderAccount->isDelinquent !== $validatedData['isDelinquent']) {
            $changes['status'] = [
                'old' => $isDelinquentMap[$stockholderAccount->isDelinquent],
                'new' => $isDelinquentMap[$validatedData['isDelinquent']]
            ];
        }

        foreach ($fieldsToTrack as $field) {

            Log::debug("Checking field: ", [
                'db' => $stockholderAccount->$field,
                'validated' => $validatedData[$field]
            ]);
            if ($stockholderAccount->$field !== $validatedData[$field]) {
                $changes[$fieldMap[$field]] = [
                    'old' => $stockholderAccount->$field === null ?  '(empty)' : $stockholderAccount->$field,
                    'new' => $validatedData[$field] === null ? '(empty)' : $validatedData[$field]
                ];
            }
        }

        return $changes;
    }


    private function validateStockCorporateUpdate(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'id'                => 'required|numeric|integer',
                'corp_rep'          => 'nullable|required_with:corp_rep_email|string|max:100',
                'corp_rep_email'    => 'nullable|required_with:corp_rep|email|max:100',
                'delinquent'        => 'nullable|in:0,1',
                'auth_signatory'    => 'nullable|string|max:200'
            ]
        );

        if ($validator->fails()) {
            $err = EApp::obj_to_array($validator->errors());
            throw new ValidationErrorException($err[array_key_first($err)][0], array_key_first($err));
        }
    }

    private function processStockholderUpdate(Request $request, User $userForStockholder): array| \Illuminate\Http\JsonResponse
    {

        $userId = $userForStockholder->id;
        $newStockholder = $request->input('stockholder');

        $this->validateStockholderUpdateBusinessRules($userForStockholder, $request);
        $isCorpAccount = $userForStockholder->stockholder->accountType === 'corp';


        $validatedData = [
            'email' => $request->input('email'),
            'stockholder' => $newStockholder,
            'voteInPerson' => $isCorpAccount
                ? $request->input('vote_in_person')
                : 'stockholder',
            'authorizedSignatory' => $isCorpAccount
                ? $request->input('auth_signatory')
                : $newStockholder,
        ];

        Log::debug(
            'validated' . json_encode($validatedData)
        );

        $changeTracker = $this->trackStockholderChanges($userForStockholder, $validatedData);

        if (empty($changeTracker)) {
            return response()->json(['message' => 'No changes detected.'], 400);
        }

        $this->updateStockholderEmail($userForStockholder, $validatedData);
        $this->updateStockholderInfo($userForStockholder, $validatedData);
        $this->logoutUpdatedUser($userId);

        ActivityController::log([
            'activityCode' => '00029',
            'accountNo' => $userForStockholder->stockholder->accountNo,
            'userId' => $userId,
            'data' => json_encode($changeTracker)
        ]);

        return ['message' => 'Account updated successfully'];
    }


    private function updateStockholderInfo(User $userForStockholder, array $validatedData): void
    {
        $stockholderInfo = $userForStockholder->stockholder;
        $isCorporateAccount = $stockholderInfo->accountType === 'corp';

        $stockholderInfo->stockholder = $validatedData['stockholder'];
        $stockholderInfo->authorizedSignatory = $isCorporateAccount ? $validatedData['authorizedSignatory'] : null;
        $stockholderInfo->voteInPerson = $isCorporateAccount
            ? strtolower($validatedData['voteInPerson'] ?? 'stockholder')
            : 'stockholder';

        $stockholderInfo->save();
    }

    /**
     * Update stockholder email with validation and conflict checking.
     * 
     * Validates permission, checks for email conflicts based on authorized signatory,
     * and persists the email change.
     * 
     * @param User $userForStockholder User to update
     * @param array $validatedData Validated data containing email and authorizedSignatory
     * @throws ValidationErrorException If permission denied or email conflict found
     */
    private function updateStockholderEmail(User $userForStockholder, array $validatedData): void
    {

        $userId = $userForStockholder->id;

        $newEmail = $this->normalizeEmail($validatedData['email']);
        $newAuthorizedSignatory = $validatedData['authorizedSignatory'] ?? null;
        $currentUserSignatory = $this->getAuthorizedSignatory($userForStockholder);

        // Only process if email actually changed or if the authorized signatory changed (for corporate accounts)
        if ($userForStockholder->email === $newEmail && $currentUserSignatory === $newAuthorizedSignatory) {
            return;
        }


        $this->validateEmailEditPermission();
        $this->validateEmailConflicts($userId, $newEmail, $newAuthorizedSignatory);
        $this->persistEmailChange($userForStockholder, $newEmail);
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
     * Validate that current user has permission to edit stockholder email.
     * 
     * @throws ValidationErrorException If permission denied
     */
    private function validateEmailEditPermission(): void
    {
        if (Auth::user()->cannot('edit stockholder email')) {
            throw new ValidationErrorException('You do not have permission to edit this stockholder email.');
        }
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


    /**
     * Get the authorized signatory name for a user based on their role.
     * 
     * @param User $user User to get signatory for
     * @return string|null Signatory name or null if not applicable
     * @throws ValidationErrorException If user role is unsupported
     */
    private function getAuthorizedSignatory(User $user): ?string
    {
        return match ($user->role) {
            'stockholder' => $user->stockholder->accountType === 'corp'
                ? $user->stockholder->authorizedSignatory
                : $user->stockholder->stockholder,
            'corp-rep' => $user->stockholderAccount->stockholder->accountType === 'corp'
                ? $user->stockholderAccount->corpRep
                : $user->stockholderAccount->stockholder->stockholder,
            default => throw new ValidationErrorException("Authorized signatory information is required to validate email changes for this account.")
        };
    }

    /**
     * Validate that new email doesn't conflict with existing user accounts.
     * Throws exception if email belongs to another account with different signatory.
     * 
     * @param int $userId ID of the user being updated
     * @param string|null $newEmail Email to validate
     * @param string|null $newSignatory Signatory name for the new email
     * @throws ValidationErrorException If email conflict found
     */
    private function validateEmailConflicts(int $userId, ?string $newEmail, ?string $newSignatory): void
    {

        $emailConflictUsers = User::with('stockholder', 'stockholderAccount')
            ->where('email', $newEmail)
            ->whereNot('id', $userId)
            ->get();


        if ($emailConflictUsers->count() === 0) {
            return;
        }


        foreach ($emailConflictUsers as $conflictingUser) {
            $conflictingUserSignatory = $this->getAuthorizedSignatory($conflictingUser);
            if ($conflictingUserSignatory !== $newSignatory) {

                Log::warning('Email conflict detected during stockholder update', [
                    'conflicting_signatory' => $conflictingUserSignatory,
                    'new_signatory' => $newSignatory,
                ]);

                throw new ValidationErrorException(
                    'The email address is already associated with another account that has a different authorized signatory. '
                        . 'Please use a different email address or update the authorized signatory for the existing account. '
                        . 'Existing email belongs to ' . $conflictingUserSignatory
                );
            }
        }
    }

    private function logoutUpdatedUser(string $id): void
    {

        // Invalidate the session for the user
        Session::getHandler()->destroy($id);

        User::where('id', $id)->update([
            'password' => null,
            'otp' => null,
            'otpValid' => false
        ]);
    }

    private function validateStockholderUpdateBusinessRules(User $userForStockholder, Request $request)
    {

        $validator = Validator::make(
            $request->all(),

            [
                'id'                => 'required|integer',
                'stockholder'       => 'required|string|max:100',
                'email'             => [
                    'required',
                    'email',
                    'max:100'
                ],
                'vote_in_person' => [
                    Rule::requiredIf(
                        $userForStockholder->stockholder->accountType === 'corp'
                    ),
                    'in:stockholder,corp-rep'
                ],
                'auth_signatory' => [
                    Rule::requiredIf(
                        $userForStockholder->stockholder->accountType === 'corp'
                    ),
                ],
            ]
        );

        if ($validator->fails()) {
            $err = EApp::obj_to_array($validator->errors());
            throw new ValidationErrorException($err[array_key_first($err)][0]);
        }
    }

    private function trackStockholderChanges(User $userForStockholder, array $validatedData)
    {


        $stockholderInfo = $userForStockholder->stockholder;

        $changes = [];

        // Track user changes
        if ($userForStockholder->email !== $validatedData['email']) {
            $changes['email'] = [
                'old' => $userForStockholder->email,
                'new' => $validatedData['email']
            ];
        }


        // Track non-member changes
        $fieldsToTrack = ['stockholder', 'voteInPerson', 'authorizedSignatory'];

        //map field names
        $fieldMap = [
            'stockholder' => 'stockholder',
            'voteInPerson' => 'Online Voter',
            'authorizedSignatory' => 'authorized signatory'
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
}
