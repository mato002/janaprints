<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;

class MarginLeakageAnalysisService
{
    /**
     * @param  array{company_id?: int, days?: int}  $filters
     * @return array<string, mixed>
     */
    public function analyze(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $days = (int) ($filters['days'] ?? 90);

        $comparisons = PrintEstimateActualComparison::query()
            ->where('company_id', $companyId)
            ->where('comparison_status', EstimateActualComparisonStatus::Completed)
            ->where('compared_at', '>=', now()->subDays($days))
            ->get();

        $categories = [
            'material' => ['field' => 'material_cost_variance', 'percent' => 'material_cost_variance_percent', 'label' => __('Material')],
            'ink' => ['field' => 'ink_cost_variance', 'percent' => 'ink_cost_variance_percent', 'label' => __('Ink')],
            'machine' => ['field' => 'machine_cost_variance', 'percent' => 'machine_cost_variance_percent', 'label' => __('Machine / process')],
            'labour' => ['field' => 'labour_cost_variance', 'percent' => 'labour_cost_variance_percent', 'label' => __('Labour')],
            'overhead' => ['field' => 'overhead_cost_variance', 'percent' => 'overhead_cost_variance_percent', 'label' => __('Overhead')],
        ];

        $drivers = [];
        foreach ($categories as $key => $meta) {
            $totalVariance = round($comparisons->sum(fn ($c) => (float) $c->{$meta['field']}), 2);
            $avgPercent = $comparisons->pluck($meta['percent'])->filter()->avg();

            $drivers[$key] = [
                'category' => $key,
                'label' => $meta['label'],
                'total_variance' => $totalVariance,
                'average_variance_percent' => $avgPercent !== null ? round((float) $avgPercent, 3) : null,
            ];
        }

        uasort($drivers, fn ($a, $b) => abs($b['total_variance']) <=> abs($a['total_variance']));

        return [
            'top_profit_erosion_drivers' => array_values(array_slice($drivers, 0, 5)),
            'recommendations' => $this->recommendations($drivers),
            'comparison_count' => $comparisons->count(),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $drivers
     * @return list<string>
     */
    protected function recommendations(array $drivers): array
    {
        $notes = [];
        $top = reset($drivers);

        if ($top && ($top['category'] ?? '') === 'ink' && abs((float) ($top['average_variance_percent'] ?? 0)) > 10) {
            $notes[] = __('Ink underestimation is a top profit erosion driver — review ink profiles and yield assumptions.');
        }
        if ($top && ($top['category'] ?? '') === 'material') {
            $notes[] = __('Material cost variance is eroding margin — review wastage and consumption accuracy.');
        }
        if ($top && ($top['category'] ?? '') === 'machine') {
            $notes[] = __('Machine/process variance is high — review setup efficiency and machine rates.');
        }
        if ($top && ($top['category'] ?? '') === 'labour') {
            $notes[] = __('Labour overruns detected — review run-time and staffing assumptions.');
        }
        if ($top && ($top['category'] ?? '') === 'overhead') {
            $notes[] = __('Overhead allocation variance is significant — review overhead rules.');
        }

        return $notes;
    }
}
