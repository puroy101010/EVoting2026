<?php

namespace App\Http\Controllers;

use Exception;


use Illuminate\Support\Facades\Log;

use App\Models\Attendance;
use App\Models\StockholderAccount;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use App\Http\Requests\ExportAttendanceRequest;
use App\Http\Requests\IndexAttendanceRequest;
use App\Http\Requests\PrintAttendanceSummaryRequest;
use App\Models\UsedBoardOfDirectorAccount;
use App\Services\UtilityService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToArray;

use Barryvdh\DomPDF\Facade\Pdf as PDF;

class AttendanceController extends Controller
{
    public function index(IndexAttendanceRequest $request)
    {

        Log::info("Accessing AttendanceController@index");

        $attendance = $this->getAttendanceData();

        ActivityController::log(['activityCode' => '00056']);

        Log::info('Successfully accessed AttendanceController@index');
        return view('admin.attendance', ['attendance' => $attendance]);
    }


    public function getAttendanceData(): \Illuminate\Support\Collection
    {
        $attendance = UsedBoardOfDirectorAccount::with('stockholderAccount.stockholder:stockholderId,stockholder', 'ballot')->get()
            ->sortBy(function ($item) {
                return optional($item->stockholderAccount->stockholder)->stockholder;
            });

        return $attendance;
    }



    public function export(ExportAttendanceRequest $request)
    {

        try {

            Log::info('Exporting attendance data');

            $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
            $attendance = $this->getAttendanceData() ?? collect();


            $attendanceCountStockholderOnline = $attendance->filter(function ($item) {
                return optional($item->ballot)->ballotType === 'person';
            })->count();

            $attendanceCountProxy = $attendance->filter(function ($item) {
                return optional($item->ballot)->ballotType === 'proxy';
            })->count();


            $attendanceDataFormatted = $this->formatAttendanceExcel($attendance);

            ActivityController::log(['activityCode' => '00108']);

            Log::info('Successfully exported attendance data');

            return Excel::download(new AttendanceExport($attendanceDataFormatted, $attendance->count(), $attendanceCountStockholderOnline, $attendanceCountProxy), 'Attendance ' . $currentDateTime . '.xlsx');
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Error exporting attendance data');

            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }


    private function formatAttendanceExcel(Collection $attendance): array
    {
        return $attendance->map(function ($record, $index) {
            return [
                'Account No.' => optional($record->stockholderAccount)->accountKey,
                'Shareholder' => optional($record->stockholderAccount->stockholder)->stockholder,
            ];
        })->toArray();
    }


    public function print(PrintAttendanceSummaryRequest $request)
    {


        Log::info('Generating attendance summary PDF');

        $attendance = $this->getAttendanceData() ?? collect();
        $attendanceCountStockholderOnline = $attendance->filter(function ($item) {
            return optional($item->ballot)->ballotType === 'person';
        })->count();

        $attendanceCountProxy = $attendance->filter(function ($item) {
            return optional($item->ballot)->ballotType === 'proxy';
        })->count();



        ActivityController::log(['activityCode' => '00140']);

        Log::info('Successfully generated attendance summary');

        return view('prints.print_attendance_summary', [
            'userInfo' => Auth::user(),
            'total' => $attendance->count(),
            'stockholderOnline' => $attendanceCountStockholderOnline,
            'proxy' => $attendanceCountProxy,
        ]);

        $pdf = PDF::loadView('prints.print_attendance_summary', [
            'userInfo' => Auth::user(),
            'total' => $attendance->count(),
            'stockholderOnline' => $attendanceCountStockholderOnline,
            'proxy' => $attendanceCountProxy,
        ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $pdf->setOptions(['enable_php' => true]);


        $fileName = 'Attendance Summary'  . '_' . now()->format('Ymd_His') . '.pdf';
        $directory = storage_path('app/private/exports/results');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $filePath = $directory . '/' . $fileName;
        $output = $pdf->output();
        file_put_contents($filePath, $output);

        Log::info('Attendance summary PDF saved to server', ['file_path' => $filePath]);

        ActivityController::log(['activityCode' => '00140']);

        Log::info('Successfully generated attendance summary PDF');

        return $pdf->stream('Attendance Summary ' . now()->format('Ymd_His') . '.pdf');







        return view('prints.print_attendance_summary', [
            'userInfo' => Auth::user(),
            'total' => $attendance->count(),
            'stockholderOnline' => $attendanceCountStockholderOnline,
            'proxy' => $attendanceCountProxy,
        ]);
    }
}
