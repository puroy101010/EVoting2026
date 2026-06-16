<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EApp;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use App\Services\UtilityService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if (Auth::check()) {

            $user = Auth::user();

            if ($user->adminAccount->isActive === 0) {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($user->role === 'stockholder' or $user->role === 'corp-rep' or $user->role === 'non-member') {

                return redirect('user');
            }


            if (Auth::user()->role === "admin") {

                return redirect('admin');
            }
        }



        return view('admin/login');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function login(LoginRequest $request)
    {




        try {

            Log::info("Admin login attempt by: " . $request->input('email'));

            $user = User::where('email', $request->input('email'))
                ->whereIn('role', ['superadmin', 'admin'])
                ->select('id', 'password')
                ->first();

            Log::info("User fetched for admin login: " . ($user ? $user->id : 'not found'));

            if (!$user) {

                Log::info("Admin login failed - email not found: " . $request->input('email'));
                ActivityController::log(['activityCode' => '00034', 'email' => $request->input('email')]);
                return response()->json(["message" => "Email or password is incorrect"], 400);
            }

            if (!password_verify($request->input('password'), $user->password)) {
                Log::info("Admin login failed - incorrect password for email: " . $request->input('email'));
                ActivityController::log(['activityCode' => '00034', 'email' => $request->input('email')]);
                return response()->json(["message" => "Email or password is incorrect"], 400);
            }

            if (isset($user->adminAccount) && $user->adminAccount->isActive === 0) {
                ActivityController::log(['activityCode' => '00034', 'email' => $request->input('email')]);
                return response()->json(["message" => "Account is inactive. Please contact your administrator."], 400);
            }


            Auth::loginUsingId($user->id, false);

            Log::info("Admin login successful for: " . $request->input('email'));
            ActivityController::log(['activityCode' => '00001']);
            return response()->json(["message" => "success"]);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Admin login error');
            return response()->json(["message" => "An error occurred. Please try again later."], 400);
        }
    }
}
