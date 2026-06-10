<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilityClass;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;

class ProfitabilityAnalyticsService
{
    /**
     * @param  array{company_id?: int, period?: string, days?: int}  $filters
     * @return array<string, mixed>
     */
    public function summarize(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $days = (int) ($filters['days'] ?? 90);
        $period = $filters['period'] ?? 'month';

        $jobs = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', now()->subDays($days)->toDateString())
            ->orderBy('snapshot_date')
            ->get();

        $buckets = $jobs->groupBy(fn ($row) => $this->bucketKey($row->snapshot_date, $period));

        $series = $buckets->map(function ($group, $key) {
            $revenue = $group->sum(fn ($r) => (float) $r->revenue);
            $cost = $group->sum(fn ($r) => (float) $r->total_cost);
            $profit = $group->sum(fn ($r) => (float) $r->gross_profit);

            return [
                'period' => $key,
                'revenue' => round($revenue, 2),
                'cost' => round($cost, 2),
                'profit' => round($profit, 2),
                'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 3) : null,
                'accuracy' => round((float) $group->avg('profitability_score'), 2),
                'job_count' => $group->count(),
            ];
        })->values()->all();

        return [
            'period_granularity' => $period,
            'total_revenue' => round($jobs->sum(fn ($r) => (float) $r->revenue), 2),
            'total_cost' => round($jobs->sum(fn ($r) => (float) $r->total_cost), 2),
            'total_profit' => round($jobs->sum(fn ($r) => (float) $r->gross_profit), 2),
            'average_margin' => $this->weightedMargin($jobs),
            'excellent_jobs' => $jobs->where('profitability_class', ProfitabilityClass::Excellent)->count(),
            'loss_making_jobs' => $jobs->where('profitability_class', ProfitabilityClass::LossMaking)->count(),
            'series' => $series,
        ];
    }

    protected function bucketKey(mixed $date, string $period): string
    {
        $carbon = \Illuminate\Support\Carbon::parse($date);

        return match ($period) {
            'day' => $carbon->toDateString(),
            'week' => $carbon->startOfWeek()->toDateString(),
            'quarter' => $carbon->format('Y').'-Q'.ceil($carbon->month / 3),
            'year' => $carbon->format('Y'),
            default => $carbon->format('Y-m'),
        };
    }

    protected function weightedMargin($rows): ?float
    {
        $revenue = $rows->sum(fn ($r) => (float) $r->revenue);
        $profit = $rows->sum(fn ($r) => (float) $r->gross_profit);

        return $revenue > 0 ? round(($profit / $revenue) * 100, 3) : null;
    }
}
