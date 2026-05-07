<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AmendmentProxyMasterlistExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $rows;

    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Account',
            'Proxy Form No',
            'Assignor',
            'Assignor Account',
            'Assignor Account Type',
            'Assignee',
            'Assignee Account',
            'Assignee Account Type',
            'Status',
            'Remarks'
        ];
    }

    public function map($row): array
    {
        return [
            $row['account'] ?? '',
            $row['formNo'] ?? '',
            $row['assignor'] ?? '',
            $row['assignorAccount'] ?? '',
            $row['assignorType'] ?? '',
            $row['assignee'] ?? '',
            $row['assigneeAccount'] ?? '',
            $row['assigneeType'] ?? '',
            $row['status'] ?? '',
            $row['remarks'] ?? ''
        ];
    }
}
