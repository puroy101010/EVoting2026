<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BODExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        // Define the Excel column headers
        return [
            'Assignee',
            'Proxy Form No',
            'Assignor (Stockholder)',
            'Assignor',
            'Account Status',
            'Auditor',
            'Verified',
        ];

    }
}
