<?php

namespace App\Support\Commercial\Reports\Exports\Contracts;

use App\Models\CommercialReportExport;
use Generator;

interface CommercialReportExporter
{
    public function module(): string;

    /**
     * @return list<string>
     */
    public function columns(CommercialReportExport $export): array;

    /**
     * @return Generator<int, array<string, string>>
     */
    public function rows(CommercialReportExport $export): Generator;

    public function title(CommercialReportExport $export): string;

    public function subtitle(CommercialReportExport $export): string;
}
