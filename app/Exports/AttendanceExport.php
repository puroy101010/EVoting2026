<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class AttendanceExport implements FromCollection, WithHeadings, WithTitle, WithCustomStartCell
{
    protected $data;
    protected $totalAttendance;
    protected $stockholderOnline;
    protected $proxyVoting;

    public function __construct(array $data, int $totalAttendance = 0, int $stockholderOnline = 0, int $proxyVoting = 0)
    {
        $this->data = $data;
        $this->totalAttendance = $totalAttendance;
        $this->stockholderOnline = $stockholderOnline;
        $this->proxyVoting = $proxyVoting;
    }

    public function collection()
    {


        // Add title and statistics at the top
        $collection = collect(

            [
                ['Stockholder Attendance Report'],
                [''],
                ['Total Votes Cast:', $this->totalAttendance],
                ['Stockholders Voted Online:', $this->stockholderOnline],
                ['Stockholders Voted by Proxy:', $this->proxyVoting],
                [''],
                ['Account No.', 'Shareholder'],
            ]
        );

        // Add the actual data
        $collection = $collection->merge($this->data);

        return $collection;
    }

    public function headings(): array
    {
        // Define the Excel column headers - they will appear after the title and stats
        return [
            // 'Account No.',
            // 'Shareholder',
        ];
    }

    public function title(): string
    {
        return 'Stockholder Voted Attendance';
    }

    public function startCell(): string
    {
        return 'A1';
    }
}
