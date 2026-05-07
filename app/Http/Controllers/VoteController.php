<?php

namespace App\Http\Controllers;


use App\Services\VoteService;
use Illuminate\Http\Request;


class VoteController extends Controller
{
    public function index(Request $request)
    {

        $voteService = new VoteService();

        return $voteService->index($request);
    }
}
