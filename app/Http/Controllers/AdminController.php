<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPassswordAdminAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreAdminAccountRequest;
use App\Http\Requests\UpdateAdminAccountRequest;
use App\Services\AdminAccountService;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\User;
use App\Services\UtilityService;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{

    protected $adminAccountService;

    public function __construct(AdminAccountService $adminAccountService)
    {

        $this->adminAccountService = $adminAccountService;
    }



    public function index()
    {

        $data = User::with(['adminAccount' => function ($query) {
            $query->withTrashed();
        }, 'roles'])->where('role', 'admin')->selectRaw('id, email')->get();
        $roles = Role::all();
        return view('admin.admin_account', ["admins" => $data, "roles" => $roles, 'title' => 'Admin Accounts']);
    }



    public function store(StoreAdminAccountRequest $request)
    {
        return $this->adminAccountService->store($request);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {

            $user = Auth::user();

            if (! $user) {

                Log::error("Change Password: Unauthenticated user attempted to change password.");
                return response()->json(["message" => "Unauthenticated."], 401);
            }

            $currentPassword    = $request->input('current_password');
            $newPassword        = $request->input('new_password');

            // verify current password
            if (! Hash::check($currentPassword, $user->password)) {
                Log::info("Change Password:  User {$user->id} provided an invalid current password.");
                return response()->json(["message" => "Current password is invalid."], 400);
            }

            // hash and save new password inside transaction    
            DB::beginTransaction();

            $user->password = Hash::make($newPassword);
            $user->save();





            ActivityController::log(['activityCode' => '00035', 'userId' => $user->id]);

            DB::commit();

            // re-login user into the current session and regenerate session id + CSRF token
            Auth::loginUsingId($user->id);
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            Log::info("Change Password:  User {$user->id} has changed their password successfully.");

            return response()->json(["message" => "Password has been changed successfully."], 200);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Change Password: Error occurred while changing password.');
            return response()->json([], 500);
        }
    }


    public function logout(Request $request)
    {

        if (Auth::check()) {

            ActivityController::log(['activityCode' => '00008', 'userId' => Auth::id()]);

            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            Log::info('User has logged out successfully.');
            Log::info("Redirecting user to admin login page.");

            return redirect()->route('admin.login');
        }
    }

    public function update(UpdateAdminAccountRequest $request, $id)
    {
        return $this->adminAccountService->update($request, $id);
    }

    public function resetPassword(ResetPassswordAdminAccountRequest $request)
    {


        return $this->adminAccountService->resetPassword($request);
    }
}
