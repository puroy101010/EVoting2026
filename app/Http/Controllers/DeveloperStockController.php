<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexDeveloperStockRequest;
use App\Http\Requests\IndexStockRequest;
use App\Services\DeveloperStockService;
use Illuminate\Http\Request;

class DeveloperStockController extends Controller
{

    public DeveloperStockService $stockService;

    public function __construct()
    {
        $this->stockService = new DeveloperStockService();
    }

    public function index(IndexDeveloperStockRequest $request)
    {
        return $this->stockService->index($request);
    }
}
