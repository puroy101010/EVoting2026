<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditOnlineAccountRequest;
use App\Http\Requests\IndexOnlineAccountRequest;
use App\Http\Requests\ShowOnlineAccountRequest;
use App\Models\User;
use Illuminate\Http\Request;


class OnlineAccountController extends Controller
{
    public function index(IndexOnlineAccountRequest $request)
    {
        return (new \App\Services\OnlineAccountService())->index($request);
    }

    public function update(EditOnlineAccountRequest $request, string $email)
    {
        return (new \App\Services\OnlineAccountService())->update($request, $email);
    }

    public function showStocks(ShowOnlineAccountRequest $request, string $email)
    {
        return (new \App\Services\OnlineAccountService())->showStocks($request, $email);
    }

    public function showProxies(Request $request, string $email)
    {
        return (new \App\Services\OnlineAccountService())->showProxies($email);
    }
}
