<?php

namespace App\Support\Reports\Concerns;

trait BuildsIntelligenceSections
{
    /**
     * @return array<string, mixed>
     */
    protected function kpi(string $label, string $value, string $icon = 'chart-pie', ?string $hint = null, bool $pending = false): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'hint' => $hint ?? ($pending ? __('Module not ready') : null),
            'pending' => $pending,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pendingSection(string $title): array
    {
        return [
            'type' => 'placeholder',
            'title' => $title,
            'message' => __('Module not ready'),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $kpis
     * @return array<string, mixed>
     */
    protected function kpiSection(string $title, array $kpis): array
    {
        return ['type' => 'kpis', 'title' => $title, 'items' => $kpis];
    }

    /**
     * @param  list<string>  $columns
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    protected function tableSection(string $title, array $columns, array $rows, bool $empty = false): array
    {
        return [
            'type' => 'table',
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'empty' => $empty || $rows === [],
        ];
    }

    /**
     * @param  list<array{cells: list<string>, url?: ?string}>  $rows
     * @return array<string, mixed>
     */
    protected function drilldownTable(string $title, array $columns, array $rows): array
    {
        return [
            'type' => 'drilldown',
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'empty' => $rows === [],
        ];
    }

    /**
     * @param  list<array{label: string, value: int, max: int}>  $points
     * @return array<string, mixed>
     */
    protected function chartSection(string $title, array $points, ?string $hint = null): array
    {
        return [
            'type' => 'chart',
            'title' => $title,
            'hint' => $hint,
            'points' => $points,
            'empty' => $points === [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $top
     * @param  list<array<string, mixed>>  $bottom
     * @return array<string, mixed>
     */
    protected function performersSection(string $title, array $top, array $bottom): array
    {
        return [
            'type' => 'performers',
            'title' => $title,
            'top' => $top,
            'bottom' => $bottom,
        ];
    }
}
