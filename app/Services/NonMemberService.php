<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Models\NonMemberAccount;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NonMemberService
{
    public function store(Request $request)
    {
        try {

            Log::info('Creating new non-member account...');

            $this->validateStoreBusinessRules($request);

            DB::beginTransaction();

            $user = $this->createUser($request->email);
            $nonMember = $this->createNonMemberAccount($user, $request);

            $this->logCreationActivity($user, $nonMember);

            DB::commit();

            Log::info('Non-member account created successfully.', [
                'userId' => $user->id,
                'accountNo' => $nonMember->nonmemberAccountNo,
                'email' => $user->email,
                'isGM' => $nonMember->isGM
            ]);

            return response()->json([
                'message' => 'Non-member account was created successfully.',
                'data' => [
                    'id' => $user->id,
                    'accountNo' => $nonMember->nonmemberAccountNo,
                    'email' => $user->email
                ]
            ], 201);
        } catch (\InvalidArgumentException $e) {

            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create non-member account: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e
            ]);
            return response()->json([
                'message' => 'A server error occurred. Please try again later.'
            ], 500);
        }
    }




    private function validateStoreBusinessRules($request)
    {
        $isGm = (int)$request->input('isGM');
        $accountNumber = $request->input('account_number');

        // GM account business rules
        if ($accountNumber === '0000' && $isGm !== 1) {
            Log::warning('Attempt to use reserved GM account number for non-GM account.', [
                'account_number' => $accountNumber,
                'isGM' => $isGm
            ]);
            throw new \InvalidArgumentException('Account number 0000 is reserved for GM accounts only.');
        }

        if ($isGm === 1) {
            if ($accountNumber !== '0000') {
                Log::warning('Attempt to create GM account with non-reserved account number.', [
                    'account_number' => $accountNumber,
                    'isGM' => $isGm
                ]);
                throw new \InvalidArgumentException('GM accounts must use account number 0000.');
            }

            if ($this->gmAccountExists()) {
                Log::warning('Attempt to create multiple GM accounts.', [
                    'account_number' => $accountNumber,
                    'isGM' => $isGm
                ]);
                throw new \InvalidArgumentException('Only one GM account is allowed in the system.');
            }
        }
    }

    private function gmAccountExists()
    {
        return NonMemberAccount::withTrashed()
            ->where('isGM', 1)
            ->exists();
    }

    private function createUser($email)
    {
        return User::create([
            'email' => $email,
            'role' => 'non-member',
            'createdBy' => Auth::id()
        ]);
    }

    private function logCreationActivity(User $user, NonMemberAccount $nonMember)
    {


        $remarks = sprintf(
            'Created non-member account —- %s (%s %s, %s)',
            $nonMember->nonmemberAccountNo,
            $nonMember->firstName,
            $nonMember->lastName,
            $user->email
        );

        ActivityController::log([
            'remarks' => $remarks,
            'activityCode' => '00027',
            'userId' => $user->id
        ]);

        Log::info('Non-member account creation activity logged.', [
            'userId' => $user->id,
            'remarks' => $remarks
        ]);
    }

    private function createNonMemberAccount(User $user, Request $request)
    {
        return $user->nonMemberAccount()->create([
            'nonmemberAccountNo' => $request->input('account_number'),
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'middleName' => $request->input('middleName') ? trim($request->input('middleName')) : null,
            'isActive' => (int)$request->input('status'),
            'isGM' => (int)$request->input('isGM'),
            'createdBy' => Auth::id()
        ]);
    }






    public function update(Request $request)
    {
        try {
            Log::info('Updating non-member account...', ['id' => $request->id]);

            $user = $this->findUserWithNonMemberAccount($request->id);
            $validatedData = $request->validated();


            // Track changes before updating
            $changeTracker = $this->trackChanges($user, $validatedData);


            DB::beginTransaction();

            // Update user and non-member data
            $this->updateUserData($user, $validatedData);
            $this->updateNonMemberData($user->nonMemberAccount, $validatedData);

            // Handle status changes
            $statusChange = $this->handleStatusChange($user->nonMemberAccount, (int)$validatedData['status']);

            $mergedChanges = array_merge($changeTracker, $statusChange ?? []);

            // Check if any changes were made
            if (!$this->hasChanges($user, $statusChange)) {
                DB::rollBack();
                Log::info('No changes detected for non-member account update.', [
                    'id' => $request->id,
                    'changes' => $changeTracker
                ]);
                return response()->json(['message' => 'No changes were made.'], 422);
            }

            // Save changes
            $user->save();
            $user->nonMemberAccount->save();

            // Log activity with detailed change remarks
            $this->logUpdateActivity($user, $mergedChanges);

            DB::commit();

            Log::info('Non-member account updated successfully.', [
                'userId' => $user->id,
                'accountNo' => $user->nonMemberAccount->nonmemberAccountNo,
                'email' => $user->email
            ]);

            return response()->json(['message' => 'Non-member account has been updated successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Non-member account not found for update.', ['id' => $request->id]);
            return response()->json(['message' => 'Non-member account not found.'], 404);
        } catch (ValidationErrorException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Non-member: ' . $e->getMessage()], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update non-member account: ' . $e->getMessage(), [
                'id' => $request->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e
            ]);
            return response()->json(['message' => 'A server error occurred. Please try again later.'], 500);
        }
    }


    private function findUserWithNonMemberAccount($id)
    {
        return User::with(['nonMemberAccount' => function ($query) {
            $query->withTrashed();
        }])
            ->where('role', 'non-member')
            ->findOrFail($id);
    }




    private function trackChanges(User $user, array $validatedData)
    {
        $changes = [];
        $nonMember = $user->nonMemberAccount;

        // Track user changes
        if ($user->email !== $validatedData['email']) {
            $changes['email'] = [
                'old' => $user->email,
                'new' => $validatedData['email']
            ];
        }


        // Track non-member changes
        $fieldsToTrack = ['firstName', 'middleName', 'lastName'];

        //map field names
        $fieldMap = [
            'firstName' => 'first name',
            'middleName' => 'middle name',
            'lastName' => 'last name',

        ];

        foreach ($fieldsToTrack as $field) {
            if ($nonMember->$field !== $validatedData[$field]) {
                $changes[$fieldMap[$field]] = [
                    'old' => $nonMember->$field ?: '(empty)',
                    'new' => $validatedData[$field] ?: '(empty)'
                ];
            }
        }

        return $changes;
    }


    private function updateUserData(User $user, array $validatedData)
    {
        $user->email = $validatedData['email'];
    }


    private function updateNonMemberData(NonMemberAccount $nonMember, array $validatedData)
    {
        $nonMember->firstName = $validatedData['firstName'];
        $nonMember->middleName = $validatedData['middleName'];
        $nonMember->lastName = $validatedData['lastName'];
    }


    private function handleStatusChange(NonMemberAccount $nonMember, int $newStatus)
    {
        $currentStatus = (int)$nonMember->isActive;

        if ($currentStatus === (int)$newStatus) {


            return null;
        }

        $statusChange['status'] = [
            'old' => $currentStatus === 1 ? 'active' : 'inactive',
            'new' => (int)$newStatus  === 1 ? 'active' : 'inactive'
        ];

        if ($newStatus === 0 && !$nonMember->isActive) {
            // Deactivate account

            if (Auth::user()->cannot('delete non member')) {
                Log::warning('Non-member: Unauthorized attempt to deactivate non-member account', [
                    'accountNo' => $nonMember->nonmemberAccountNo
                ]);
                throw new ValidationErrorException('You do not have permission to deactivate non-member accounts.');
            }

            Log::info('Deactivating non-member account.', [
                'accountNo' => $nonMember->nonmemberAccountNo
            ]);
            $nonMember->isActive = false;
        } elseif ($newStatus === 1 && $nonMember->isActive) {
            // Activate account


            if (Auth::user()->cannot('restore non member')) {
                Log::warning('Non-member: Unauthorized attempt to restore non-member account', [
                    'accountNo' => $nonMember->nonmemberAccountNo
                ]);
                throw new ValidationErrorException('You do not have permission to restore non-member accounts.');
            }

            Log::info('Activating non-member account.', [
                'accountNo' => $nonMember->nonmemberAccountNo
            ]);
            $nonMember->isActive = true;
        }



        return $statusChange;
    }


    private function hasChanges(User $user, $statusChange)
    {
        return $user->isDirty() ||
            $user->nonMemberAccount->isDirty() ||
            $statusChange !== null;
    }

    private function logUpdateActivity(User $user, array $changes)
    {


        ActivityController::log([
            'activityCode' => '00028',
            'remarks' => "{$user->nonMemberAccount->nonmemberAccountNo} ({$user->nonMemberAccount->firstName} {$user->nonMemberAccount->lastName})",
            'data' => json_encode($changes),
            'userId' => $user->id
        ]);
    }
}
