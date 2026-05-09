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


    private function handleAdditionalStock(Request $request, $stockholderInfo)
    {

        $this->checkIfSuffixExists($request, $stockholderInfo);
        $this->validateCorpRepRule($stockholderInfo, $request);
        $user = $this->createUserForAdditionalStockholderAccount($stockholderInfo, $request->corp_rep_email);
        $this->createAdditionalStockholderAccount($stockholderInfo, $user, $request);


        ActivityController::log(['activityCode' => '00098', 'remarks' => $stockholderInfo->accountNo . '-' . $request->suffix, 'userId' => $user->id]);

        return response()->json(['message' => 'A new stock has been added successfully.'], 200);
    }

    private function createAdditionalStockholderAccount($stockholderInfo, $user, $request)
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

    private function createUserForAdditionalStockholderAccount($stockholderInfo, $corpRepEmail): User
    {
        $corpRepEmail = $corpRepEmail === null ? null : strtolower($corpRepEmail);
        return User::create([
            'email' => $stockholderInfo->accountType === 'corp' ? $corpRepEmail : null,
            'role' => 'corp-rep',
            'createdBy' => Auth::id()
        ]);
    }




    /**
     * Validate corporate representative rules for stockholder accounts.
     *
     * Used when creating or updating corporate representative information.
     */
    private function validateCorpRepRule($stockholderInfo, $request)
    {
        if ($stockholderInfo->accountType !== 'corp') {
            return;
        }

        $corpRep = strtolower($request->corp_rep);
        $corpRepEmail = strtolower($request->corp_rep_email);
        $accountNo = $stockholderInfo->accountNo;

        if (empty($corpRepEmail)) {
            return;
        }

        // Check if corporate representative email is same as stockholder email
        if ($stockholderInfo->user->email === $corpRepEmail) {
            Log::info('The stockholder\'s email and the corporate representative\'s email cannot be the same.', [
                'stockholder_email' => $stockholderInfo->user->email,
                'corp_rep_email' => $corpRepEmail
            ]);
            throw new ValidationErrorException('The stockholder\'s email and the corporate representative\'s email cannot be the same.');
        }

        // Check if email exists for a different stockholder account
        $emailExistsForDifferentAccount = User::where('email', $corpRepEmail)
            ->where(function ($query) use ($accountNo) {
                // Check if user is a stockholder with different account number
                $query->whereHas('stockholder', function ($subQuery) use ($accountNo) {
                    $subQuery->where('accountNo', '!=', $accountNo);
                })
                    // OR check if user is a corp-rep for a different stockholder account
                    ->orWhere(function ($subQuery) use ($accountNo) {
                        $subQuery->where('role', 'corp-rep')
                            ->whereHas('stockholderAccount.stockholder', function ($nestedQuery) use ($accountNo) {
                                $nestedQuery->where('accountNo', '!=', $accountNo);
                            });
                    });
            })
            ->first();

        if ($emailExistsForDifferentAccount) {
            if ($emailExistsForDifferentAccount->stockholder) {
                Log::info('Email already exists for another stockholder: ' . $corpRepEmail, [
                    'stockholder' => $emailExistsForDifferentAccount->stockholder->toArray(),
                ]);
                throw new ValidationErrorException('Email already exists for another stockholder with account no. ' . $emailExistsForDifferentAccount->stockholder->accountNo);
            }

            if ($emailExistsForDifferentAccount->stockholderAccount) {
                Log::info('Email already exists for another stockholder account: ' . $corpRepEmail, [
                    'stockholder_account' => $emailExistsForDifferentAccount->stockholderAccount->toArray(),
                ]);
                throw new ValidationErrorException('Email already exists for another stockholder with account no. ' . $emailExistsForDifferentAccount->stockholderAccount->accountKey);
            }
        }

        // Check if corporate representative name matches existing email for same account
        $existingCorpRepForSameAccount = User::where('email', $corpRepEmail)
            ->where('role', 'corp-rep')
            ->whereHas('stockholderAccount.stockholder', function ($query) use ($accountNo) {
                $query->where('accountNo', $accountNo);
            })
            ->first();

        if ($existingCorpRepForSameAccount !== null) {
            $existingCorpRepName = strtolower($existingCorpRepForSameAccount->stockholderAccount->corpRep);

            if ($existingCorpRepName !== $corpRep) {
                Log::info("The corporate representative name for the existing email does not match the provided name. Existing corporate representative: "
                    . $existingCorpRepForSameAccount->stockholderAccount->corpRep);
                throw new ValidationErrorException("The corporate representative name for the existing email does not match the provided name. Existing corporate representative: "
                    . $existingCorpRepForSameAccount->stockholderAccount->corpRep);
            }
        }

        Log::info('Corporate representative email validation passed');
    }

    private function checkIfSuffixExists(Request $request, $stockholderInfo)
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

            $user = User::with('stockholder')->findOrFail($request->id);

            if ($user->role === 'stockholder') {
                $result = $this->processStockholderRoleUpdate($request);
                Log::info('Stockholder updated successfully', ['user_id' => $request->id]);
                DB::commit();
                return $result;
            }

            if ($user->stockholderAccount->stockholder->accountType === 'corp') {
                $result = $this->processStockholderAccountCorpRepRoleUpdate($request);

                DB::commit();

                Log::info('Corporate representative account updated successfully', ['user_id' => $request->id]);
                return $result;
            }


            $result = $this->processStockholderAccountIndvRoleUpdate($request);
            DB::commit();

            Log::info('Stockholder account for individual updated successfully', ['user_id' => $request->id]);
            return $result;
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            Log::info('Validation error in stockholder creation', [
                'error' => $e->getMessage(),
                'request_data' => $request->only(['account_number', 'stockholder', 'account_type'])
            ]);

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error occurred while updating stockholder.");
            return response()->json([], 500);
        }
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
    private function processStockholderAccountCorpRepRoleUpdate(Request $request)
    {

        $this->validateStockholderAccountCorporateUpdate($request);

        $id             = $request->input('id');
        $corpRep        = $request->input('corp_rep');
        $corpRepEmail   = $request->input('corp_rep_email') === null ? null : strtolower($request->input('corp_rep_email'));
        $isDelinquent   = (int)$request->input('delinquent');
        $authorizedSignatory = $request->input('auth_signatory');

        $validatedData = [
            'email' => $corpRepEmail,
            'corpRep' => $corpRep,
            'isDelinquent' => (int)$isDelinquent,
            'authSignatory' => $authorizedSignatory
        ];


        $userForCorpRep = User::with('stockholder')->findOrFail($id);

        $stockholderInfo = $userForCorpRep->stockholderAccount->stockholder;

        $this->validateCorpRepRule($stockholderInfo, $request);


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


        $this->logoutUpdatedUser($id);
        ActivityController::log([
            'activityCode' => '00029',
            'accountNo' => $stockholderAccount->accountKey,
            'userId' => $id,
            'data' => json_encode($changeTracker)
        ]);


        Log::info('Corporate representative account updated successfully', [
            'user_id' => $id,
            'changes' => $changeTracker
        ]);

        return response()->json(['message' => 'Account updated successfully', 200]);
    }

    private function validateUpdateCorpRepEmailPermission($userForCorpRep, $validatedData)
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

    private function trackCorpRepChanges($userForCorpRep, $validatedData)
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


    private function validateStockholderAccountCorporateUpdate(Request $request)
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

    private function processStockholderRoleUpdate(Request $request): array| \Illuminate\Http\JsonResponse
    {

        $userId = $request->input('id');

        $userForStockholder = User::with('stockholder', 'stockholderAccount')->where('id', $userId)->where('role', 'stockholder')->first();

        $this->validateStockholderUpdateBusinessRules($userForStockholder, $request);


        $validatedData = [
            'email' => $request->input('email'),
            'stockholder' => $request->input('stockholder'),
            'voteInPerson' => $userForStockholder->stockholder->accountType === 'corp' ? $request->input('vote_in_person') : 'stockholder',
            'authorizedSignatory' => $request->input('auth_signatory'),
        ];

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

        return array('message' => 'Account updated successfully');
    }


    private function updateStockholderInfo(User $userForStockholder, array $validatedData): void
    {

        $stockholderInfo = $userForStockholder->stockholder;

        $stockholderInfo->stockholder = $validatedData['stockholder'];

        // Only update the Authorized Signatory and Vote in Person if the account type is corporate. 
        $stockholderInfo->authorizedSignatory = $userForStockholder->stockholder->accountType === 'corp' ? $validatedData['authorizedSignatory'] : null;

        // Default the vote in person to 'stockholder' for individual accounts.
        $voteInPerson   = $validatedData['voteInPerson'] === null ? null : strtolower($validatedData['voteInPerson']);
        $stockholderInfo->voteInPerson = $userForStockholder->stockholder->accountType === 'corp' ? $voteInPerson : 'stockholder';

        $stockholderInfo->save();
    }

    private function updateStockholderEmail(User $userForStockholder, array $validatedData)
    {

        Log::debug('Updating stockholder email');

        $email = $validatedData['email'] === null ? null : strtolower($validatedData['email']);
        $authorizedSignatory = $validatedData['authorizedSignatory'] ?? null;

        if ($userForStockholder->email !== $email) {

            Log::debug('Stockholder email change detected', [
                'user_id' => $userForStockholder->id,
                'old_email' => $userForStockholder->email,
                'new_email' => $email
            ]);

            if (Auth::user()->cannot('edit stockholder email')) {
                throw new ValidationErrorException('You do not have permission to edit this stockholder email.');
            }

            $existingUsers = User::with('stockholder', 'stockholderAccount')->where('email', $email)->whereNot('id', $userForStockholder->id)->get();

            foreach ($existingUsers as $existingUser) {


                switch ($userForStockholder->role) {
                    case 'stockholder':
                        $accountOwner  = $existingUser->stockholder->accountType === 'corp' ? $existingUser->stockholder->authorizedSignatory : $existingUser->stockholder->stockholder;
                        break;

                    case 'corp-rep':
                        $accountOwner  = $userForStockholder->stockholderAccount->corpRep ?? null;
                        break;

                    default:
                        throw new \Exception('Invalid user role: ' . $userForStockholder->role);
                }



                Log::debug('Checking existing user for email conflict', [
                    'existing_account_owner' => $accountOwner,
                    'new_authorized_signatory' => $authorizedSignatory,
                ]);

                if ($accountOwner !== $authorizedSignatory) {
                    throw new ValidationErrorException('The email address is already associated with another account that has a different authorized signatory. Please use a different email address or update the authorized signatory for the existing account. Existing email belongs to ' . $accountOwner);
                }
            }
        }

        $userForStockholder->email = $email;



        $userForStockholder->save();
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
