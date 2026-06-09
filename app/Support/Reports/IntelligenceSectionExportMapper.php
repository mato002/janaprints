<?php

namespace App\Support\Reports;

class IntelligenceSectionExportMapper
{
    /**
     * @param  list<array<string, mixed>>  $sections
     * @return list<list<string>>
     */
    public function rows(array $sections): array
    {
        $rows = [];

        foreach ($sections as $section) {
            $this->appendSection($rows, $section);
        }

        return $rows;
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  array<string, mixed>  $section
     */
    protected function appendSection(array &$rows, array $section): void
    {
        $title = (string) ($section['title'] ?? __('Section'));
        $type = (string) ($section['type'] ?? 'kpis');

        match ($type) {
            'kpis' => $this->appendKpis($rows, $title, $section['items'] ?? []),
            'drilldown', 'table' => $this->appendDrilldown($rows, $title, $section),
            'split' => $this->appendSplit($rows, $title, $section),
            'trends' => $this->appendTrends($rows, $section),
            'performers' => $this->appendPerformers($rows, $title, $section),
            'placeholder' => $rows[] = [$title, (string) ($section['message'] ?? __('Module not ready')), ''],
            default => null,
        };
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  list<array<string, mixed>>  $items
     */
    protected function appendKpis(array &$rows, string $title, array $items): void
    {
        foreach ($items as $item) {
            $rows[] = [
                $title,
                (string) ($item['label'] ?? ''),
                (string) ($item['value'] ?? ''),
            ];
        }
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  array<string, mixed>  $section
     */
    protected function appendDrilldown(array &$rows, string $title, array $section): void
    {
        foreach ($section['rows'] ?? [] as $row) {
            $cells = $row['cells'] ?? $row;
            $rows[] = [
                $title,
                is_array($cells) ? implode(' | ', array_map('strval', $cells)) : (string) $cells,
                '',
            ];
        }
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  array<string, mixed>  $section
     */
    protected function appendSplit(array &$rows, string $title, array $section): void
    {
        $this->appendKpis($rows, $title, $section['kpis'] ?? []);

        foreach ($section['tables'] ?? [] as $table) {
            $this->appendDrilldown($rows, (string) ($table['title'] ?? $title), $table);
        }
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  array<string, mixed>  $section
     */
    protected function appendTrends(array &$rows, array $section): void
    {
        foreach ($section['charts'] ?? [] as $chart) {
            $chartTitle = (string) ($chart['title'] ?? $section['title'] ?? __('Trend'));

            foreach ($chart['points'] ?? [] as $point) {
                $rows[] = [
                    $chartTitle,
                    (string) ($point['label'] ?? ''),
                    (string) ($point['value'] ?? ''),
                ];
            }
        }
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  array<string, mixed>  $section
     */
    protected function appendPerformers(array &$rows, string $title, array $section): void
    {
        if (! empty($section['groups'])) {
            foreach ($section['groups'] as $group) {
                foreach ($group['items'] ?? [] as $item) {
                    $rows[] = [
                        (string) ($group['heading'] ?? $title),
                        (string) ($item['label'] ?? ''),
                        (string) ($item['value'] ?? ''),
                    ];
                }
            }

            return;
        }

        foreach (['top', 'bottom'] as $bucket) {
            foreach ($section[$bucket] ?? [] as $item) {
                $rows[] = [
                    $title.' ('.$bucket.')',
                    (string) ($item['label'] ?? ''),
                    (string) ($item['value'] ?? ''),
                ];
            }
        }
    }
}
