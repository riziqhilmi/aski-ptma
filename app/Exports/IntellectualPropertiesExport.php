<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IntellectualPropertiesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $records)
    {
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Member',
            'Tipe Akun',
            'Institusi',
            'Nomor Permohonan',
            'Tanggal Permohonan',
            'Pencipta',
            'Pemegang Hak',
            'Jenis HKI',
            'Nomor Pencatatan',
            'Judul Ciptaan',
            'Tanggal Pertama Diumumkan',
            'Jangka Waktu Pelindungan',
        ];
    }

    public function map($record): array
    {
        return [
            $record->user->name,
            $record->user->memberProfile?->account_type === 'institution' ? 'Institusi' : 'Individu',
            $record->user->memberProfile?->institution_name ?? '-',
            $record->application_number,
            $record->application_date?->format('Y-m-d'),
            $record->creator,
            $record->rights_holder,
            $record->ip_type,
            $record->recordation_number ?? '-',
            $record->title,
            $record->first_published_at?->format('Y-m-d'),
            $record->protection_period,
        ];
    }
}
