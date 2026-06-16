<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AmendmentProxyActiveExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize
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
            'Proxy Form No',
            'Assignor',
            'Assignor Account',
            'Assignee',
            'Assignee Account',
            'Account Status',
            'Vote Status',
            'Audited',
            'Auditor',
            'Audited At'
        ];
    }

    public function map($row): array
    {
        return [
            $row['proxyFormNo'] ?? '',
            $row['assignor'] ?? '',
            $row['assignorAccountNo'] ?? '',
            $row['assignee'] ?? '',
            $row['assigneeAccountNo'] ?? '',
            $row['isDelinquent'] ?? '',
            $row['vote'] ?? '',
            $row['audited'] ?? '',
            $row['auditor'] ?? '',
            $row['auditedAt'] ?? ''
        ];
    }
}
