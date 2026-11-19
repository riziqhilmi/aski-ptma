<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IntellectualPropertiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->application_date?->format('d/m/Y'),
            $record->creator,
            $record->rights_holder,
            $record->ip_type,
            $record->recordation_number ?? '-',
            $record->title,
            $record->first_published_at?->format('d/m/Y'),
            $record->protection_period,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '123D32']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 26, 'B' => 15, 'C' => 34, 'D' => 22, 'E' => 20, 'F' => 28,
            'G' => 28, 'H' => 20, 'I' => 22, 'J' => 42, 'K' => 26, 'L' => 34,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => static function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(1, $sheet->getHighestRow());

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:L{$lastRow}");
                $sheet->getDefaultRowDimension()->setRowHeight(21);
                $sheet->getRowDimension(1)->setRowHeight(34);
                $sheet->getStyle("A1:L{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A2:L{$lastRow}")->getAlignment()->setWrapText(true);

                if ($lastRow > 1) {
                    $table = new Table("A1:L{$lastRow}", 'DataHKI');
                    $style = new TableStyle(TableStyle::TABLE_STYLE_MEDIUM7);
                    $style->setShowRowStripes(true);
                    $table->setStyle($style);
                    $sheet->addTable($table);
                }
            },
        ];
    }
}
