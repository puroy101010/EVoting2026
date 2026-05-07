<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;

use Exception;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{

    public function index(Request $request)
    {

        if (Auth::check()) {

            return redirect('/');
        }

        return view('user.login');
    }
}
