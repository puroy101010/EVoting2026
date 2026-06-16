<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportAdminBallotRequest;
use App\Http\Requests\IndexBallotRequest;
use App\Http\Requests\PreviewBallotRequest;
use App\Models\Ballot;
use App\Models\User;
use App\Services\ConfigService;
use App\Services\UtilityService;
use App\Services\VoteService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
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
