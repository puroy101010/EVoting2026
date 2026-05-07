<?php

namespace App\Http\Controllers;

use App\Models\HasRoleModel;
use App\Http\Requests\UpdateRoleRequest;;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $roles = \Spatie\Permission\Models\Role::all();
        return view('admin.roles', ['title' => 'Roles', 'roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = $request->input('id'); // Get the role ID from the request

        $permissions = Permission::with(['roles' => function ($query) use ($id) {
            $query->where('id', $id); // Filter by role ID
        }])
            ->get(['id', 'name', 'module', 'description'])
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'module' => $permission->module,
                    'description' => $permission->description,
                    'has_role' => $permission->roles->isNotEmpty(), // true if related to role
                ];
            })
            ->toArray();

        return response()->json(['permissions' => $permissions]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


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

    public function permissions(Request $request) {}

    /**
     * Update role permissions via AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRoleRequest $request)
    {


        try {
            // Validate the request
            $request->validate([
                'role_id' => 'required|integer|exists:roles,id',
                'permissions' => 'required|array',
                'permissions.*.permission_id' => 'required|integer|exists:permissions,id',
                'permissions.*.granted' => 'required|in:true,false',
            ]);

            Log::info("Updating role permissions", [
                'role_id' => $request->input('role_id'),
                'permissions' => $request->input('permissions'),
            ]);

            $roleId = $request->input('role_id');
            $permissions = $request->input('permissions');


            if ((int)$roleId === 1) {


                if (Auth::user()->adminAccount->isDefault === 0) {

                    Log::debug("User is not authorized to modify superadmin role", [
                        'user_id' => Auth::user()->id,
                        'role_id' => $roleId,
                        'permissions' => $permissions,
                    ]);
                    Log::warning("Attempt to update superadmin role permissions blocked", [
                        'role_id' => $roleId,
                        'permissions' => $permissions,
                    ]);
                    return response()->json([
                        'message' => 'The superadmin role cannot be modified for security reasons.'
                    ], 403);
                }
            }
            $role = \Spatie\Permission\Models\Role::findById($roleId);

            if (!$role) {
                Log::error("Role not found", [
                    'role_id' => $roleId,
                    'permissions' => $permissions
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found.'
                ], 404);
            }


            $changesArr = [];

            // Process each permission
            foreach ($permissions as $permissionData) {
                $permissionId = $permissionData['permission_id'];
                $isGranted = $permissionData['granted'] === 'true' ? true : false;
                $permission = Permission::find($permissionId);

                if ($permission) {
                    if ($isGranted  === true) {
                        if (!$role->hasPermissionTo($permission)) {
                            $role->givePermissionTo($permission);
                            $changesArr[$permission->name] = [
                                'old' => 'unauthorized',
                                'new' => 'granted',
                            ];
                        }
                    } else {
                        if ($role->hasPermissionTo($permission)) {
                            $changesArr[$permission->name] = [
                                'old' => 'granted',
                                'new' => 'unauthorized',
                            ];
                            $role->revokePermissionTo($permission);
                        }
                    }
                }
            }

            if (empty($changesArr)) {

                Log::info("No changes made to permissions for role", [
                    'role_id' => $roleId,
                    'permissions' => $permissions
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'No changes made to permissions.',
                    'reload' => false
                ], 204);
            }


            ActivityController::log([
                'activityCode' => '00113',
                'remarks' => "Role permissions updated for <span class=\"font-weight-bold\">$role->name",
                'roleId' => $roleId,
                'data' => json_encode($changesArr)
            ]);


            Log::info("Role permissions updated successfully", [
                'role_id' => $roleId,
                'changes' => $changesArr
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully.',
                'reload' => false
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update role permissions", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'An error occurred while updating permissions. Please try again.'
            ], 500);
        }
    }


    public function users($id)
    {


        return HasRoleModel::leftJoin('admin_accounts', 'admin_accounts.userId', '=', 'model_has_roles.model_id')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('role_id', $id)
            ->select('admin_accounts.userId', 'admin_accounts.firstName', 'admin_accounts.lastName', 'admin_accounts.isActive', 'users.email')
            ->get()->toArray();
    }
}
