<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditOnlineAccountRequest;
use App\Models\User;
use Illuminate\Http\Request;


class OnlineAccountController extends Controller
{
    public function index(Request $request)
    {
        return (new \App\Services\OnlineAccountService())->index($request);
    }

    public function update(EditOnlineAccountRequest $request, string $email)
    {
        return (new \App\Services\OnlineAccountService())->update($request, $email);
    }

    public function showStocks(Request $request, string $email)
    {
        return (new \App\Services\OnlineAccountService())->showStocks($email);
    }

    public function showProxies(Request $request, string $email)
    {
        return (new \App\Services\OnlineAccountService())->showProxies($email);
    }
}
