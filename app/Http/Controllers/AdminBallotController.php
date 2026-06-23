<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportAdminBallotRequest;
use App\Http\Requests\IndexBallotRequest;
use App\Http\Requests\PreviewBallotRequest;

use App\Services\AdminBallotService;


class AdminBallotController extends Controller
{
    public AdminBallotService $adminBallotService;

    public function __construct()
    {
        $this->adminBallotService = new AdminBallotService();
    }
    public function index(IndexBallotRequest $request)
    {
        return $this->adminBallotService->index($request);
    }
    public function preview(PreviewBallotRequest $request, $id)
    {

        return $this->adminBallotService->preview($request, $id);
    }

    public function export(ExportAdminBallotRequest $request)
    {
        return $this->adminBallotService->export($request);
    }
}
