<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IntellectualPropertiesVisualizationSheet implements FromArray, WithCharts, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(
        private readonly array $trend,
        private readonly string $group,
        private readonly array $filters = [],
    ) {
    }

    public function array(): array
    {
        $filterDescription = collect([
            filled($this->filters['institution'] ?? null) ? 'Institusi: '.$this->filters['institution'] : null,
            filled($this->filters['type'] ?? null) ? 'Jenis: '.$this->filters['type'] : null,
            filled($this->filters['period_start'] ?? null) ? 'Mulai: '.$this->filters['period_start'] : null,
            filled($this->filters['period_end'] ?? null) ? 'Sampai: '.$this->filters['period_end'] : null,
        ])->filter()->implode(' | ');

        return [
            ['VISUALISASI DATA HKI'],
            ['Tren jumlah data berdasarkan tanggal permohonan'],
            ['Kelompok waktu', $this->group === 'year' ? 'Tahunan' : 'Bulanan'],
            ['Filter diterapkan', $filterDescription !== '' ? $filterDescription : 'Semua data'],
            ['Jumlah data', array_sum(array_column($this->trend, 'total'))],
            ['Dibuat pada', now()->format('d/m/Y H:i')],
            [$this->trend === [] ? 'Belum ada data sesuai filter.' : 'Grafik interaktif tersedia di bawah.'],
        ];
    }

    public function charts(): array
    {
        if ($this->trend === []) {
            return [];
        }

        $lastDataRow = count($this->trend) + 1;
        $label = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Data Tren'!\$B\$1", null, 1)];
        $categories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Data Tren'!\$A\$2:\$A\${$lastDataRow}", null, count($this->trend))];
        $values = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Data Tren'!\$B\$2:\$B\${$lastDataRow}", null, count($this->trend))];

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            [0],
            $label,
            $categories,
            $values,
            null,
            true,
            DataSeries::STYLE_LINEMARKER,
        );

        $chart = new Chart(
            'hkiTrend',
            new Title('Tren Data HKI'),
            new Legend(Legend::POSITION_BOTTOM),
            new PlotArea(null, [$series]),
            true,
            'gap',
            new Title('Periode'),
            new Title('Jumlah HKI'),
        );
        $chart->setTopLeftPosition('A9');
        $chart->setBottomRightPosition('N29');

        return [$chart];
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 72];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('B4:N4');
        $sheet->mergeCells('B7:N7');
        $sheet->freezePane('A3');
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getRowDimension(1)->setRowHeight(34);
        $sheet->getRowDimension(2)->setRowHeight(26);
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '123D32']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A2:B2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '475569']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A3:A6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '123D32']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF4EF']],
        ]);
        $sheet->getStyle('A1:N29')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B4:B7')->getAlignment()->setWrapText(true);

        return [];
    }

    public function title(): string
    {
        return 'Visualisasi';
    }
}
