<?php

namespace App\Support\Hr;

use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrKpiExporter
{
    public function __construct(
        protected HrKpiScopeResolver $scopeResolver,
        protected HrWorkforceIntelligenceService $intelligence,
        protected TabularExportWriter $writer,
    ) {}

    /**
     * @return list<array<int, string>>
     */
    public function rows(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);

        $rows = [
            [__('From'), $resolved['scope']->fromDate],
            [__('To'), $resolved['scope']->toDate],
            [__('Dimension'), $resolved['filters']['dimension']],
            ['', ''],
        ];

        return array_merge($rows, $this->intelligence->exportRows($resolved['scope']));
    }

    public function download(Request $request, string $format): StreamedResponse
    {
        return $this->writer->download(
            $format,
            'hr-kpi-'.now()->format('Y-m-d'),
            [__('Metric'), __('Value')],
            $this->rows($request),
            __('HR KPI Report'),
        );
    }
}
