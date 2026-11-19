<?php

namespace App\Exports;

use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IntellectualPropertiesTrendDataSheet implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    public function __construct(private readonly array $trend, private readonly string $group)
    {
    }

    public function array(): array
    {
        $rows = [['Periode', 'Jumlah HKI']];

        foreach ($this->trend as $item) {
            $period = $item['label'];
            if ($this->group !== 'year' && preg_match('/^\d{4}-\d{2}$/', $period)) {
                $period = CarbonImmutable::createFromFormat('!Y-m', $period)->locale('id')->translatedFormat('M Y');
            }
            $rows[] = [$period, (int) $item['total']];
        }

        if ($this->trend === []) {
            $rows[] = ['Belum ada data', 0];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 20];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '123D32']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => static function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $sheet->freezePane('A2');
                $sheet->getDefaultRowDimension()->setRowHeight(22);
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getStyle("A1:B{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                if ($lastRow > 1) {
                    $table = new Table("A1:B{$lastRow}", 'TrenHKI');
                    $style = new TableStyle(TableStyle::TABLE_STYLE_MEDIUM7);
                    $style->setShowRowStripes(true);
                    $table->setStyle($style);
                    $sheet->addTable($table);
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Data Tren';
    }
}
