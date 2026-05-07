<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Models\NonMemberAccount;
use App\Models\Stockholder;
use App\Models\StockholderAccount;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{

    public function index()
    {

        Log::info('Fetching stockholder accounts with related stockholder and user data');
        $stocks = StockholderAccount::with(['stockholder', 'user', 'usedBodAccount', 'usedAmendmentAccount', 'proxyBoard', 'proxyAmendment'])
            ->get();

        return view('admin.stocks', ['stocks' => $stocks]);
    }
}
