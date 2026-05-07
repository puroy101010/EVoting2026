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

class DeveloperStockService
{

    public function index()
    {

        Log::info("Accessed developer stock page");

        $stocks = StockholderAccount::with(['stockholder', 'user', 'usedBodAccount.ballot', 'usedAmendmentAccount.ballot', 'proxyBoard', 'proxyAmendment'])
            ->get();

        ActivityController::log([
            'activityCode' => '00138'
        ]);

        Log::info("Successful access to developer stock page");


        return view('admin.developer_stocks', ['stocks' => $stocks]);
    }
}
