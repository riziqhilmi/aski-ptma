<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IntellectualPropertiesImportTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'member_email', 'application_number', 'application_date', 'creator', 'rights_holder',
            'ip_type', 'recordation_number', 'title', 'first_published_at', 'protection_period', 'work_type',
        ];
    }

    public function array(): array
    {
        return [[
            'email-member@example.com', 'EC00202600001', '2026-01-10', 'Nama Pencipta', 'Nama Pemegang Hak',
            'Hak Cipta', '000123456', 'Judul Ciptaan', '2026-01-15', 'Sesuai dokumen', 'Program Komputer',
        ]];
    }
}
