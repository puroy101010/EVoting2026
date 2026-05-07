<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class MemberExport implements FromCollection, WithHeadings
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
            'Stockholder',
            'AccountNo',
            'Suffix',
            'accountKey',
            'AccountType',
            'Email',
            'Vote InPerson',
            'Authorized Signatory',
            'Corporate Representative',
            'Corporare Representative Email',
            'Delinquent',
            
            '(BOD Proxy) Proxy Form No',
            '(BOD Proxy) Assignee',
            '(BOD Proxy) Assignor',

            '(Amendment Proxy) Ref No',
            '(Amendment Proxy) Assignee',
            '(Amendment Proxy) Assignor',

            'Quorum'
        ];
    }
}
