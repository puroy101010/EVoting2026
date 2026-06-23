<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationErrorException;
use App\Http\Requests\StoreStockholderOnlineBallotRequest;
use App\Http\Requests\SubmitStockholderOnlineRequest;
use App\Http\Requests\SummaryStockholderOnlineRequest;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use App\Services\StockholderOnlineBallotService;

class StockholderOnlineBallotController extends Controller
{

    private StockholderOnlineBallotService $stockholderOnlineBallotService;

    public function __construct(StockholderOnlineBallotService $stockholderOnlineBallotService)
    {
        $this->stockholderOnlineBallotService = $stockholderOnlineBallotService;
    }

    public function store(StoreStockholderOnlineBallotRequest $request)
    {

        return $this->stockholderOnlineBallotService->store($request);
    }

    public function show(Request $request, string $id)
    {
        return $this->stockholderOnlineBallotService->show($request, $id);
    }








    public function submit(SubmitStockholderOnlineRequest $request)
    {
        return $this->stockholderOnlineBallotService->submit($request);
    }

    public function summary(SummaryStockholderOnlineRequest $request)
    {

        return $this->stockholderOnlineBallotService->summary($request);
    }
}
