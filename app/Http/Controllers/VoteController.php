<?php

namespace App\Http\Controllers;

use App\Services\OnlineAccountService;
use App\Services\VoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class VoteController extends Controller
{
    public function index(Request $request)
    {
        return (new VoteService())->index($request);
    }
}
