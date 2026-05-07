<?php

namespace App\Http\Controllers;


use App\Http\Requests\EditAmendmentRequest;
use App\Http\Requests\IndexAmendmentRequest;
use App\Http\Requests\StoreAmendmentRequest;
use App\Services\AmendmentService;

class AmendmentController extends Controller
{

    private $amendmentService;

    public function __construct(AmendmentService $amendmentService)
    {
        $this->amendmentService = $amendmentService;
    }


    public function index(IndexAmendmentRequest $request)
    {
        return $this->amendmentService->index($request);
    }


    public function store(StoreAmendmentRequest $request)
    {
        return $this->amendmentService->store($request);
    }


    public function update(EditAmendmentRequest $request)
    {
        return $this->amendmentService->update($request);
    }
}
