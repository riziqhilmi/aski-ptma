<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class IntellectualPropertiesVisualizationExport implements WithMultipleSheets
{
    public function __construct(
        private readonly array $trend,
        private readonly string $group,
        private readonly array $filters = [],
    ) {
    }

    public function sheets(): array
    {
        return [
            new IntellectualPropertiesVisualizationSheet($this->trend, $this->group, $this->filters),
            new IntellectualPropertiesTrendDataSheet($this->trend, $this->group),
        ];
    }
}
