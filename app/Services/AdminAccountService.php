<?php

namespace App\Services;

use App\Models\AdminAccount;
use App\Http\Controllers\ActivityController;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AdminAccountService
{

    public function store($request)
    {
        try {

            $email = $request->email;
            $password = $request->password;
            $role = $request->role;

            DB::beginTransaction();

            $user = $this->createUser($email, $password);
            $this->createAdminAccount($user, $request);

            $user->assignRole($role);



            $this->logStoreActivity($user, $request);
            DB::commit();
            Log::info('Admin account created successfully', [
                'userId' => $user->id,
                'email' => $email,
                'role' => $role
            ]);

            return response()->json([
                'message' => 'Admin account created successfully.',
            ], 201);
        } catch (Exception $e) {
            Log::error('Error creating admin account', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'A server error occurred. Please try again later.'], 500);
        }
    }

    private function createUser($email, $password)
    {
        return User::create([
            'email' => $email,
            'role' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'createdBy' => Auth::id()
        ]);
    }

    private function createAdminAccount($user, $request)
    {
        $user->adminAccount()->create([
            'firstName' => $request->firstName,
            'middleName' => $request->middleName,
            'lastName' => $request->lastName,
            'createdBy' => Auth::id()
        ]);
    }

    private function logStoreActivity($user, $request)
    {

        ActivityController::log([
            'activityCode' => '00041',
            'email' => $user->email,
            'userId' => $user->id,
            'remarks' => sprintf(
                'Added a new admin account with the role "%s" —- <span class="font-weight-bold">%s %s %s</span>',
                htmlspecialchars($request->role, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($request->firstName, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($request->middleName, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($request->lastName, ENT_QUOTES, 'UTF-8')
            )
        ]);
    }


    public function update($request)
    {
        try {

            Log::info('Updating admin account', [
                'userId' => $request->id,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status
            ]);
            $userInfo = User::findOrFail($request->id);


            Log::debug('Get admin account information', [
                'userId' => $userInfo->id,
                'email' => $userInfo->email,
            ]);
            $adminInfo = $userInfo->adminAccount;



            $changeTracker = $this->trackChanges($userInfo, $request->validated());
            DB::beginTransaction();

            $this->updateAdminAccount($adminInfo, $request);



            $statusChange = UtilityService::handleStatusChange($adminInfo, $request->status, $adminInfo->adminId);

            $roleChange = $this->handleRoleChange($userInfo, $request);

            $mergedChanges = array_merge($changeTracker, $statusChange, $roleChange ?? []);


            if (!$this->hasChanges($mergedChanges)) {

                DB::rollBack();
                Log::info('No changes detected in admin account update', [
                    'userId' => $userInfo->id,
                    'email' => $userInfo->email,
                ]);
                return response()->json(['message' => 'No changes were made.'], 400);
            }

            $userInfo->save();
            $adminInfo->save();

            $this->logUpdateActivity($userInfo, $mergedChanges);
            DB::commit();
            Log::info('Admin account updated successfully', [
                'userId' => $userInfo->id,
                'email' => $userInfo->email,
                'changes' => $mergedChanges
            ]);
            return response()->json(['message' => 'Account updated successfully.'], 200);
        } catch (Exception $e) {
            Log::error('Error updating admin account', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,
                'request' => $request->all() ?? []
            ]);
            return response()->json(['message' => 'A server error occurred. Please try again later.'], 500);
        }
    }

    private function updateAdminAccount(AdminAccount $adminInfo, $request)
    {

        $adminInfo->firstName = $request->firstName;
        $adminInfo->middleName = $request->middleName;
        $adminInfo->lastName = $request->lastName;
    }


    private function trackChanges(User $user, array $validatedData)
    {
        $changes = [];


        $adminAccount = $user->adminAccount;



        // Track user changes
        if ($user->email !== $user->getOriginal('email')) {
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
            'lastName' => 'last name'
        ];

        foreach ($fieldsToTrack as $field) {
            if ($adminAccount->$field !== $validatedData[$field]) {
                $changes[$fieldMap[$field]] = [
                    'old' => $adminAccount->$field ?: '(empty)',
                    'new' => $validatedData[$field] ?: '(empty)'
                ];
            }
        }

        return $changes;
    }

    private function handleRoleChange(User $user, $request)
    {

        $currentRole = $user->getRoleNames()->first();
        $newRole = $request->role;
        $changes = [];
        if ($currentRole !== $newRole) {

            $changes['role'] = [
                'old' => $currentRole,
                'new' => $newRole
            ];
            //remove previous role
            $user->removeRole($currentRole);

            //assign new role
            $user->assignRole($newRole);
        }

        return $changes;
    }


    private function hasChanges($changes)
    {
        return !empty($changes);
    }

    private function logUpdateActivity(User $user, array $changes)
    {
        ActivityController::log([
            'activityCode' => '00042',
            'remarks' => $user->adminAccount->fullname,
            'data' => json_encode($changes),
            'email' => $user->email,
            'userId' => $user->id
        ]);

        Log::debug('Admin account update activity logged', [

            'changes' => $changes
        ]);
    }

    public function resetPassword($request)
    {
        try {

            $userToReset = User::findOrFail($request->input('id')); // Ensure the requester is authenticated

            $adminAccount = $userToReset->adminAccount;

            if (!$adminAccount) {
                Log::info("Password Reset: No admin account found for ID: " . $request->input('id'));
                return response()->json(['message' => 'Admin account not found'], 422);
            }


            $newPassword = $request->input('password');

            DB::beginTransaction();
            $userToReset = $adminAccount->user;
            $userToReset->password = Hash::make($newPassword);
            $userToReset->updatedBy = Auth::id();
            $userToReset->save();


            // ActivityController::log([
            //     'activityCode' => '00131',
            //     'remarks' => 'Reset admin account password -- <span class="font-weight-bold">' . $adminAccount->firstName . ' ' . $adminAccount->lastName . '</span>',
            //     'userId' => $adminAccount->user->id
            // ]);


            DB::commit();
            Log::info("Password Reset: Password reset successfully for admin account ID: " . $request->input('id'), [
                'hashed_password' => $userToReset->password
            ]);


            return response()->json(['message' => 'Password reset successfully.'], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Password Reset: Error occurred while resetting password.');
            return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
        }
    }
}
