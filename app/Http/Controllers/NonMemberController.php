<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexNonMemberRequest;
use App\Http\Requests\StoreNonMemberRequest;
use App\Http\Requests\UpdateNonMemberRequest;
use Exception;

use App\Models\NonMemberAccount;


use App\Services\NonMemberService;


class NonMemberController extends Controller
{


    protected $nonMemberService;

    public function __construct(NonMemberService $nonMemberService)
    {

        $this->nonMemberService = $nonMemberService;
    }


    public function index(IndexNonMemberRequest $request)
    {
        $data = NonMemberAccount::with('user')->withTrashed()->get();

        return view('admin.non_members', ["data" => $data, "title" => "Non-Members"]);
    }




    public function store(StoreNonMemberRequest $request)
    {
        return $this->nonMemberService->store($request);
    }




    public function update(UpdateNonMemberRequest $request)
    {

        return $this->nonMemberService->update($request);
    }
}
