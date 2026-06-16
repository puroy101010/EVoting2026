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
            'Stockholder',
            'Proxy Form No',
            'Assignor',
            'Assignor (account type)',
            'Assignee',
            'Assignee (email)',
            'Status',
            'Remarks'
        ];
    }

    public function map($row): array
    {
        return [
            $row['account'] ?? '',
            $row['stockholder'] ?? '',
            $row['formNo'] ?? '',
            $row['assignor'] ?? '',
            $row['assignorType'] ?? '',
            $row['assignee'] ?? '',
            $row['assigneeEmail'] ?? '',
            $row['status'] ?? '',
            $row['remarks'] ?? ''
        ];
    }
}



//   $proxyholders[] = [
//                 'id' => $proxy->proxyAmendmentId,
//                 'account' => $proxy->stockholderAccount->accountKey,
//                 'proxyAmendmentFormNo' => $proxy->proxyAmendmentFormNo,
//                 'stockholder' => $proxy->stockholderAccount->stockholder->stockholder,
//                 'assignor' => $proxy->assignorName,
//                 'assignorAccount' => $assignorAccount,
//                 'assignorType' => $assignorType,
//                 'assignee' => $proxy->assigneeName,
//                 'assigneeEmail' => $proxy->assigneeEmail,
//                 'assigneeAccount' => $assigneeAccount,
//                 'assigneeType' => $assigneeType,
//                 'status' => 'active',
//                 'remarks' => null
//             ];