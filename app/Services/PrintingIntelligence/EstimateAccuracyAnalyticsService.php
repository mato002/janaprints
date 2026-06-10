<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\EstimateVarianceClass;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EstimateAccuracyAnalyticsService
{
    /**
     * @param  array{
     *     company_id?: int,
     *     branch_id?: int|null,
     *     from?: string|null,
     *     to?: string|null,
     *     variance_class?: string|null
     * }  $filters
     * @return array<string, mixed>
     */
    public function aggregate(array $filters = []): array
    {
        $query = $this->baseQuery($filters);
        $comparisons = (clone $query)->get();

        if ($comparisons->isEmpty()) {
            return $this->emptySummary();
        }

        $completed = $comparisons->where('comparison_status', 'completed');
        $accuracyScores = $completed->pluck('accuracy_score')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
        $variancePercents = $completed->pluck('total_cost_variance_percent')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);

        $varianceClasses = $completed->groupBy(fn ($row) => $row->variance_class?->value ?? 'unknown')
            ->map->count()
            ->all();

        $accurateCount = (int) ($varianceClasses[EstimateVarianceClass::Accurate->value] ?? 0);
        $unreliableCount = (int) ($varianceClasses[EstimateVarianceClass::Unreliable->value] ?? 0);
        $majorCount = (int) ($varianceClasses[EstimateVarianceClass::MajorVariance->value] ?? 0);

        $underestimation = $completed->filter(fn ($row) => (float) $row->total_cost_variance > 0)
            ->sum(fn ($row) => (float) $row->total_cost_variance);
        $overestimation = abs($completed->filter(fn ($row) => (float) $row->total_cost_variance < 0)
            ->sum(fn ($row) => (float) $row->total_cost_variance));

        return [
            'comparison_count' => $comparisons->count(),
            'completed_count' => $completed->count(),
            'average_accuracy_score' => $accuracyScores->isNotEmpty() ? round($accuracyScores->avg(), 2) : null,
            'average_total_cost_variance_percent' => $variancePercents->isNotEmpty() ? round($variancePercents->avg(), 3) : null,
            'accurate_estimates_count' => $accurateCount,
            'accurate_estimates_percent' => $completed->count() > 0
                ? round(($accurateCount / $completed->count()) * 100, 2)
                : null,
            'major_variance_count' => $majorCount,
            'unreliable_estimates_count' => $unreliableCount,
            'total_underestimation_value' => round($underestimation, 2),
            'total_overestimation_value' => round($overestimation, 2),
            'variance_class_counts' => $varianceClasses,
            'most_underestimated_category' => $this->mostVarianceCategory($completed, 'underestimated'),
            'most_overestimated_category' => $this->mostVarianceCategory($completed, 'overestimated'),
            'top_variance_drivers' => $this->topVarianceDrivers($completed),
            'by_branch' => $this->groupMetrics($completed, 'branch_id'),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function comparisonsQuery(array $filters = []): Builder
    {
        return $this->baseQuery($filters)
            ->with(['quotation:id,quotation_number', 'jobCard:id,job_card_number', 'quotationEstimate'])
            ->latest('compared_at');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function baseQuery(array $filters): Builder
    {
        $query = PrintEstimateActualComparison::query();

        if (! empty($filters['company_id'])) {
            $query->where('company_id', (int) $filters['company_id']);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('compared_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('compared_at', '<=', $filters['to']);
        }

        if (! empty($filters['variance_class'])) {
            $query->where('variance_class', $filters['variance_class']);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptySummary(): array
    {
        return [
            'comparison_count' => 0,
            'completed_count' => 0,
            'average_accuracy_score' => null,
            'average_total_cost_variance_percent' => null,
            'accurate_estimates_count' => 0,
            'accurate_estimates_percent' => null,
            'major_variance_count' => 0,
            'unreliable_estimates_count' => 0,
            'total_underestimation_value' => 0,
            'total_overestimation_value' => 0,
            'variance_class_counts' => [],
            'most_underestimated_category' => null,
            'most_overestimated_category' => null,
            'top_variance_drivers' => [],
            'by_branch' => [],
        ];
    }

    /**
     * @param  Collection<int, PrintEstimateActualComparison>  $comparisons
     */
    protected function mostVarianceCategory(Collection $comparisons, string $direction): ?string
    {
        $categories = [
            'material' => 'material_cost_variance',
            'ink' => 'ink_cost_variance',
            'machine' => 'machine_cost_variance',
            'labour' => 'labour_cost_variance',
            'overhead' => 'overhead_cost_variance',
        ];

        $totals = [];
        foreach ($categories as $name => $field) {
            $totals[$name] = $comparisons->sum(function ($row) use ($field, $direction) {
                $value = (float) $row->{$field};

                return $direction === 'underestimated'
                    ? max(0, $value)
                    : abs(min(0, $value));
            });
        }

        arsort($totals);
        $top = array_key_first($totals);

        return $top !== null && $totals[$top] > 0 ? $top : null;
    }

    /**
     * @param  Collection<int, PrintEstimateActualComparison>  $comparisons
     * @return list<array<string, mixed>>
     */
    protected function topVarianceDrivers(Collection $comparisons): array
    {
        return $comparisons
            ->sortByDesc(fn ($row) => abs((float) ($row->total_cost_variance_percent ?? 0)))
            ->take(5)
            ->map(fn (PrintEstimateActualComparison $row) => [
                'comparison_id' => $row->id,
                'quotation_id' => $row->quotation_id,
                'production_job_card_id' => $row->production_job_card_id,
                'total_cost_variance_percent' => $row->total_cost_variance_percent !== null ? (float) $row->total_cost_variance_percent : null,
                'variance_class' => $row->variance_class?->value,
                'accuracy_score' => $row->accuracy_score !== null ? (float) $row->accuracy_score : null,
                'recommendation' => $row->recommendation,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, PrintEstimateActualComparison>  $comparisons
     * @return array<int|string, array<string, mixed>>
     */
    protected function groupMetrics(Collection $comparisons, string $field): array
    {
        return $comparisons->groupBy($field)->map(function (Collection $group) {
            $scores = $group->pluck('accuracy_score')->filter()->map(fn ($v) => (float) $v);

            return [
                'count' => $group->count(),
                'average_accuracy_score' => $scores->isNotEmpty() ? round($scores->avg(), 2) : null,
            ];
        })->all();
    }
}
