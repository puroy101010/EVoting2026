<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\Document;
use App\Services\UtilityService;
use Illuminate\Container\Attributes\Log;

class UserController extends Controller
{


    public function index()
    {
        return view('user.homepage', [
            'documents' => Document::selectRaw('documentId, title')->orderBy('documentId', 'DESC')->get(),
            'announcements' => Announcement::orderBy('announcementId', 'asc')->all()
        ]);
    }



    public function logout(Request $request)
    {



        try {


            if (Auth::check()) {

                $user = Auth::user();

                $userId = $user->id;

                Auth::logout();

                $request->session()->invalidate();

                $request->session()->regenerateToken();

                ActivityController::log(['activityCode' => '00008', 'accountNo' => $user->account_no, 'email' => $user->email, 'userId' => $userId]);

                return redirect('/');
            }

            return redirect('/');
        } catch (Exception $e) {


            UtilityService::logServerError($request, $e, "Error occurred during user logout");
            return view('errors.response', ['code' => '500', 'message' => null]);
        }
    }
}
