<?php

namespace App\Support\Reports;

use Illuminate\Http\Request;

class HrReportExporter
{
    public function __construct(
        protected HrReportScopeResolver $scopeResolver,
        protected HrReportPresenter $presenter,
    ) {}

    /**
     * @return list<array<int, string>>
     */
    public function rows(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $tabData = $this->presenter->presentTab($resolved['scope'], $resolved['tab']);
        $rows = [];

        $rows[] = [__('Report Tab'), $resolved['tab']];
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

        foreach ([
            'daily', 'departments', 'late', 'absent', 'absent_departments', 'overtime',
            'by_type', 'by_employee', 'runs', 'headcount', 'movement', 'contracts', 'training',
        ] as $key) {
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
