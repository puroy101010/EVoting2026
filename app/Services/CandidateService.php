<?php

namespace App\Services;

use App\Http\Controllers\ActivityController;
use App\Models\Candidate;
use App\Models\NonMemberAccount;
use App\Models\Stockholder;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CandidateService
{
    public function store(Request $request)
    {
        try {

            Log::info('Adding a new candidate...', [
                'firstName' => $request->input('first_name'),
                'middleName' => $request->input('middle_name'),
                'lastName' => $request->input('last_name'),
                'type' => $request->input('type'),
                'status' => $request->input('status')
            ]);
            DB::beginTransaction();
            $candidate = Candidate::firstOrCreate(
                [
                    'firstName'  => $request->input('first_name'),
                    'lastName'   => $request->input('last_name'),
                    'middleName' => $request->input('middle_name'),
                ],
                [
                    'createdBy'  => Auth::id(),
                    'type'       => $request->type,
                    'isActive'   => $request->status
                ]
            );



            if (!$candidate->wasRecentlyCreated) {
                DB::rollBack();
                Log::warning('A candidate already exists with the same name.', [
                    'firstName' => $request->input('first_name'),
                    'middleName' => $request->input('middle_name'),
                    'lastName' => $request->input('last_name')
                ]);
                return response()->json(['message' => 'Candidate already exists.'], 422);
            }


            ActivityController::log([
                'ActivityCode' => '00025',
                'candidateId' => $candidate->candidateId,
                'remarks' => "Added a new candidate --<span class=\"font-weight-bold\"> $candidate->firstName $candidate->middleName $candidate->lastName</span>"
            ]);

            DB::commit();
            Log::info('Candidate was added successfully.', [
                'candidateId' => $candidate->candidateId,
                'firstName' => $candidate->firstName,
                'middleName' => $candidate->middleName,
                'lastName' => $candidate->lastName,
                'type' => $candidate->type,
                'status' => $candidate->isActive ? 1 : 0
            ]);
            return response()->json(['message' => 'Candidate was added successfully.']);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Error adding candidate");
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            Log::info('Updating candidate information...', [
                'candidateId' => $request->input('id'),
                'firstName' => $request->input('first_name'),
                'middleName' => $request->input('middle_name'),
                'lastName' => $request->input('last_name'),
                'type' => $request->input('type'),
                'status' => $request->input('status')
            ]);

            $candidateId = $request->input('id');
            $firstName   = $request->input('first_name');
            $middleName  = $request->input('middle_name');
            $lastName    = $request->input('last_name');
            $type        = $request->input('type');
            $status      = (int) $request->input('status');

            // Check for duplicate candidates (excluding the current one)
            $duplicate = Candidate::where('firstName', $firstName)
                ->where('middleName', $middleName)
                ->where('lastName', $lastName)
                ->where('candidateId', '<>', $candidateId)
                ->exists();

            if ($duplicate) {
                Log::warning('A candidate already exists with the same name.', [
                    'firstName' => $firstName,
                    'middleName' => $middleName,
                    'lastName' => $lastName,
                    'candidateId' => $candidateId
                ]);
                return response()->json(['message' => 'Candidate already exists.'], 422);
            }

            $candidate = Candidate::withTrashed()->findOrFail($candidateId);

            DB::beginTransaction();

            $original = $candidate->getOriginal();
            $remarks = '';

            // Update fields if they have changed
            $fields = [
                'firstName'  => $firstName,
                'middleName' => $middleName,
                'lastName'   => $lastName,
                'type'       => $type,

            ];

            $changesArr = [];

            $map = [
                'firstName'  => 'first name',
                'middleName' => 'middle name',
                'lastName'   => 'last name',
                'type'       => 'type',

            ];
            foreach ($fields as $field => $value) {
                if ($candidate->$field !== $value) {
                    $changesArr[$map[$field]] = ['old' => $original[$field] ?? 'blank', 'new' => $value ?? 'blank'];

                    $candidate->$field = $value;
                }
            }



            if ($candidate->isActive && $status === 0) {

                if (Auth::user()->cannot('delete candidate')) {
                    Log::warning("Candidate: Unauthorized access attempt to delete candidate", [
                        'candidateId' => $candidateId
                    ]);
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $changesArr['status'] = ['old' => 'active', 'new' => 'inactive'];
                $candidate->isActive = false;
            } elseif (!$candidate->isActive && $status === 1) {
                $changesArr['status'] = ['old' => 'inactive', 'new' => 'active'];
                $candidate->isActive = true;
            }

            if (empty($changesArr)) {

                Log::info('No changes detected for candidate.', ['candidateId' => $candidateId]);
                DB::rollBack();
                return response()->json(['message' => 'No changes were made to the candidate.'], 422);
            }

            $candidate->save();

            ActivityController::log([
                'activityCode' => '00026',
                'remarks' => $remarks,
                'candidateId' => $candidateId,
                'data' => json_encode($changesArr)
            ]);

            DB::commit();

            Log::info('Candidate was updated successfully.', [
                'candidateId' => $candidate->candidateId,
                'firstName' => $candidate->firstName,
                'middleName' => $candidate->middleName,
                'lastName' => $candidate->lastName,
                'type' => $candidate->type,
                'status' => $candidate->isActive
            ]);
            return response()->json(['message' => 'Candidate was updated successfully.']);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Error updating candidate");
            return response()->json([], 500);
        }
    }
}
