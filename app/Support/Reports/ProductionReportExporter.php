<?php

namespace App\Support\Reports;

use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductionReportExporter
{
    public function __construct(
        protected ProductionReportScopeResolver $scopeResolver,
        protected ProductionReportPresenter $presenter,
        protected TabularExportWriter $writer,
    ) {}

    /**
     * @return list<array{0: string, 1: string}>
     */
    public function rows(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $tabData = $this->presenter->presentTab($resolved['scope'], $resolved['tab']);
        $tab = $resolved['tab'];
        $rows = [];

        $rows[] = [__('Report Tab'), $tab];
        $rows[] = [__('From'), $resolved['scope']->fromDate];
        $rows[] = [__('To'), $resolved['scope']->toDate];
        $rows[] = ['', ''];

        foreach ($this->flattenTabData($tabData) as $section) {
            $rows[] = [$section['title'], ''];
            $rows[] = $section['headers'];
            foreach ($section['rows'] as $row) {
                $rows[] = array_map(fn ($cell) => (string) $cell, (array) $row);
            }
            $rows[] = ['', ''];
        }

        return $rows;
    }

    public function download(Request $request, string $format): StreamedResponse
    {
        $resolved = $this->scopeResolver->resolve($request);

        return $this->writer->downloadRawRows(
            $format,
            'production-report-'.now()->format('Y-m-d'),
            $this->rows($request),
            __('Production Report'),
            trim(($resolved['scope']->fromDate ?? '…').' — '.($resolved['scope']->toDate ?? '…')),
        );
    }

    /**
     * @param  array<string, mixed>  $tabData
     * @return list<array{title: string, headers: list<string>, rows: list<array<int, mixed>>}>
     */
    protected function flattenTabData(array $tabData): array
    {
        $sections = [];

        if (! empty($tabData['summary'])) {
            $sections[] = [
                'title' => __('Summary'),
                'headers' => [__('Metric'), __('Value')],
                'rows' => collect($tabData['summary'])->map(fn (array $item) => [
                    $item['label'] ?? '',
                    $item['value'] ?? '',
                ])->all(),
            ];
        }

        foreach (['daily', 'departments', 'machines', 'consumption', 'waste', 'delivered', 'late', 'jobs', 'customers'] as $key) {
            if (empty($tabData[$key])) {
                continue;
            }

            $block = $tabData[$key];
            $sections[] = [
                'title' => $block['title'] ?? $key,
                'headers' => $block['columns'] ?? [],
                'rows' => $block['rows'] ?? [],
            ];
        }

        return $sections;
    }
}
