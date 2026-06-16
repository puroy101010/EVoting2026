<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Http\Requests\EditCandidateRequest;
use App\Http\Requests\IndexCandidateRequest;
use App\Http\Requests\StoreCandidateRequest;
use App\Services\CandidateService;
use App\Services\UtilityService;
use Exception;



class CandidateController extends Controller
{

    private $candidateService;

    public function __construct(CandidateService $candidateService)
    {
        $this->candidateService = $candidateService;
    }

    // completed
    public function index(IndexCandidateRequest $request)
    {
        try {

            $candidates = Candidate::orderBy('lastName', 'ASC')->get();
            return view('admin.candidates', ["candidates" => $candidates]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Failed to load candidates");
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    //done 2023-08-26
    public function store(StoreCandidateRequest $request)
    {

        return $this->candidateService->store($request);
    }

    //done 2023-08-27
    public function update(EditCandidateRequest $request)
    {

        return $this->candidateService->update($request);
    }
}
