<?php

namespace App\Services;

use App\Http\Controllers\ActivityController;
use App\Models\Agenda;


use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgendaService
{



    public function index(Request $request)
    {

        try {
            $agendas = Agenda::withTrashed()->orderBy('agendaId', 'ASC')->get();
            return view('admin.agendas', ['agendas' => $agendas]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Failed to load agendas");
            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }
    public function store(Request $request)
    {

        try {

            Log::info('Adding a new agenda...', [
                'agendaCode' => $request->code,
                'agendaDesc' => $request->agenda,
                'isActive' => $request->status
            ]);

            DB::beginTransaction();

            // Use fillable model properties and handle agendaId if needed
            $agendaData = [
                'agendaCode' => $request->code,
                'agendaDesc' => $request->agenda,
                'isActive' => (int) $request->status,
                'createdBy' => Auth::id()
            ];



            $agenda = Agenda::create($agendaData);


            ActivityController::log([
                'activityCode' => '00096',
                'agendaId' => $agenda->agendaId,
                'remarks' => $agenda->agendaCode
            ]);

            DB::commit();
            Log::info('Agenda has been added successfully.', [
                'agendaId' => $agenda->agendaId,
                'agendaCode' => $agenda->agendaCode,
                'agendaDesc' => $agenda->agendaDesc,
                'isActive' => $agenda->isActive,
                'transactionId' => $request->attributes->get('transaction_id')
            ]);

            return response()->json(['message' => 'Agenda has been added successfully.'], 201);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Failed to add agenda");
            return response()->json(['message' => 'Server error. Please try again later.'], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            Log::info('Updating agenda information...', [
                'agendaId' => $request->id,
                'agendaCode' => $request->code,
                'agendaDesc' => $request->agenda,
                'isActive' => $request->status
            ]);


            $agendaInfo = Agenda::find($request->id);
            if (!$agendaInfo) {
                Log::warning('Agenda not found for update.', ['agendaId' => $request->id]);
                return response()->json(['message' => 'Agenda not found.'], 404);
            }


            $status = (int) $request->status;
            $changesArr = [];


            DB::beginTransaction();

            // Set new value for agendaDesc
            $agendaInfo->agendaDesc = $request->agenda;



            // Track changes for agendaDesc if dirty
            if ($agendaInfo->isDirty('agendaDesc')) {

                $statusChanged = true;
                $changesArr['agenda'] = [
                    'old' => $agendaInfo->getOriginal('agendaDesc'),
                    'new' => $agendaInfo->agendaDesc
                ];
            }


            // Track status changes
            if ($agendaInfo->isActive && $status === 0) {

                if (Auth::user()->cannot('delete agenda')) {
                    Log::warning("Agenda: Unauthorized access attempt to delete agenda", [
                        'agendaId' => $agendaInfo->agendaId
                    ]);
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $changesArr['status'] = ['old' => 'active', 'new' => 'inactive'];

                $agendaInfo->isActive = false;
            } elseif (!$agendaInfo->isActive && $status === 1) {
                $changesArr['status'] = ['old' => 'inactive', 'new' => 'active'];

                $agendaInfo->isActive = true;
            }

            // Use isDirty to check if agendaDesc or status changed
            if (!$agendaInfo->isDirty()) {

                DB::rollBack();

                Log::info('No changes detected for agenda update.', [
                    'agendaId' => $agendaInfo->agendaId,
                    'agendaDesc' => $agendaInfo->agendaDesc,
                ]);

                return response()->json(['message' => 'No changes were made.'], 400);
            }


            $agendaInfo->save();

            ActivityController::log([
                'activityCode' => '00097',
                'remarks' => $agendaInfo->agendaCode,
                'data' => json_encode($changesArr),
                'agendaId' => $request->id
            ]);

            DB::commit();

            Log::info('Agenda has been successfully updated.', [
                'agendaId' => $agendaInfo->agendaId,
                'agendaCode' => $agendaInfo->agendaCode,
                'agendaDesc' => $agendaInfo->agendaDesc,
                'isActive' => $agendaInfo->isActive
            ]);
            return response()->json(['message' => 'Agenda has been successfully updated.'], 200);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, "Failed to update agenda");
            return response()->json(['message' => 'Server error. Please try again later.'], 500);
        }
    }
}
