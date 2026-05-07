<?php

namespace App\Services;

use App\Http\Controllers\ActivityController;
use App\Models\Amendment;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AmendmentService
{

    public function index($request)
    {
        try {

            $amendments = Amendment::orderBy('amendmentId', 'asc')->get();

            return view('admin.amendments', ["amendments" => $amendments, 'title' => 'Amendment Management']);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Failed to load amendments");

            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    public function store($request)
    {
        try {

            Log::info('Adding a new amendment...', [
                'amendmentCode' => $request->code,
                'amendmentDesc' => $request->amendment,
                'isActive' => $request->status
            ]);


            $status = (int) $request->status;

            DB::beginTransaction();


            $amendmentData = [
                'amendmentCode' => $request->code,
                'amendmentDesc' => $request->amendment,
                'isActive' => $status,
                'createdBy' => Auth::id()
            ];

            $amendment = Amendment::create($amendmentData);


            ActivityController::log([
                'activityCode' => '00069',
                'amendmentId' => $amendment->amendmentId,
                'remarks' => $amendment->amendmentCode
            ]);

            DB::commit();
            Log::info('Amendment has been added successfully.', [
                'amendmentId' => $amendment->amendmentId,
                'amendmentCode' => $amendment->amendmentCode,
                'amendmentDesc' => $amendment->amendmentDesc,
            ]);

            return response()->json(['message' => 'Amendment has been added successfully.'], 201);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Failed to add amendment");
            return response()->json(['message' => 'Server error. Please try again later.'], 500);
        }
    }

    public function update($request)
    {
        try {
            Log::info('Updating an amendment information...', [
                'amendmentId' => $request->id,
                'amendmentCode' => $request->code,
                'amendmentDesc' => $request->amendment
            ]);



            $amendmentInfo = Amendment::find($request->id);
            if (!$amendmentInfo) {
                Log::warning('Amendment not found for update.', ['amendmentId' => $request->id]);
                return response()->json(['message' => 'Amendment not found.'], 404);
            }


            $status = (int) $request->status;
            $changesArr = [];



            DB::beginTransaction();

            // Set new value for agendaDesc
            $amendmentInfo->amendmentDesc = $request->amendment;



            // Track changes for amendmentDesc if dirty
            if ($amendmentInfo->isDirty('amendmentDesc')) {


                $changesArr['amendment'] = [
                    'old' => $amendmentInfo->getOriginal('amendmentDesc'),
                    'new' => $amendmentInfo->amendmentDesc
                ];
            }


            // Track status changes
            if ($amendmentInfo->isActive === 1 && $status === 0) {

                if (Auth::user()->cannot('delete amendment')) {
                    Log::warning("Amendment: Unauthorized access attempt to delete amendment", [
                        'amendmentId' => $amendmentInfo->amendmentId
                    ]);
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $changesArr['status'] = ['old' => 'active', 'new' => 'inactive'];

                $amendmentInfo->isActive = 0;
            } elseif ($amendmentInfo->isActive === 0 && $status === 1) {
                $changesArr['status'] = ['old' => 'inactive', 'new' => 'active'];

                $amendmentInfo->isActive = 1;
            }

            // Use isDirty to check if amendmentDesc or status changed
            if (empty($changesArr)) {

                DB::rollBack();

                Log::info('No changes detected for amendment update.', [
                    'amendmentId' => $amendmentInfo->amendmentId,
                    'amendmentDesc' => $amendmentInfo->amendmentDesc
                ]);

                return response()->json(['message' => 'No changes were made.'], 400);
            }


            $amendmentInfo->save();

            ActivityController::log([
                'activityCode' => '00070',
                'remarks' => $request->amendment,
                'data' => json_encode($changesArr),
                'amendmentId' => $request->id
            ]);

            DB::commit();

            Log::info('Amendment has been successfully updated.', [
                'amendmentId' => $amendmentInfo->amendmentId,
                'amendmentDesc' => $amendmentInfo->amendmentDesc,
                'changes' => $changesArr
            ]);
            return response()->json(['message' => 'Amendment has been successfully updated.'], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Failed to update amendment");
            return response()->json(['message' => 'Server error. Please try again later.'], 500);
        }
    }
}
