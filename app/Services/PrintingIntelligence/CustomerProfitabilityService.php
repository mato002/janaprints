<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;

class CustomerProfitabilityService
{
    /**
     * @param  array{company_id?: int, branch_id?: int|null, days?: int}  $filters
     * @return array<string, mixed>
     */
    public function analyze(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $days = (int) ($filters['days'] ?? 90);

        $query = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Customer)
            ->where('snapshot_date', '>=', now()->subDays($days)->toDateString());

        PrintingIntelligenceScope::applyBranchScope($query, $filters);

        $rows = PrintingIntelligenceScope::dedupeSnapshotsByLatestDate(
            $query->with('customer:id,company_name')->get(),
            fn ($row) => $row->customer_id,
        );

        if ($rows->isEmpty()) {
            $jobQuery = PrintProfitabilitySnapshot::query()
                ->where('company_id', $companyId)
                ->where('snapshot_type', ProfitabilitySnapshotType::Job)
                ->where('snapshot_date', '>=', now()->subDays($days)->toDateString())
                ->whereNotNull('customer_id');

            PrintingIntelligenceScope::applyBranchScope($jobQuery, $filters);

            $rows = $jobQuery->with('customer:id,company_name')
                ->get()
                ->groupBy('customer_id')
                ->map(function ($group) {
                    $revenue = $group->sum(fn ($r) => (float) $r->revenue);
                    $profit = $group->sum(fn ($r) => (float) $r->gross_profit);

                    return (object) [
                        'customer_id' => $group->first()->customer_id,
                        'customer' => $group->first()->customer,
                        'revenue' => $revenue,
                        'gross_profit' => $profit,
                        'gross_margin_percent' => $revenue > 0 ? ($profit / $revenue) * 100 : null,
                        'metadata' => ['job_count' => $group->count()],
                    ];
                });
        }

        $ranked = collect($rows)->sortByDesc(fn ($r) => (float) ($r->gross_profit ?? 0))->values();

        return [
            'total_revenue' => round($ranked->sum(fn ($r) => (float) ($r->revenue ?? 0)), 2),
            'total_profit' => round($ranked->sum(fn ($r) => (float) ($r->gross_profit ?? 0)), 2),
            'average_margin' => $this->weightedMargin($ranked),
            'jobs_completed' => (int) $ranked->sum(fn ($r) => (int) ($r->metadata['job_count'] ?? 1)),
            'most_profitable' => $this->formatRank($ranked->first()),
            'least_profitable' => $this->formatRank($ranked->last()),
            'rankings' => $ranked->take(10)->map(fn ($r) => $this->formatRank($r))->all(),
        ];
    }

    protected function weightedMargin($rows): ?float
    {
        $revenue = $rows->sum(fn ($r) => (float) ($r->revenue ?? 0));
        $profit = $rows->sum(fn ($r) => (float) ($r->gross_profit ?? 0));

        return $revenue > 0 ? round(($profit / $revenue) * 100, 3) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function formatRank(mixed $row): ?array
    {
        if ($row === null) {
            return null;
        }

        $revenue = (float) ($row->revenue ?? 0);
        $profit = (float) ($row->gross_profit ?? 0);
        $jobs = (int) ($row->metadata['job_count'] ?? 1);

        return [
            'customer_id' => $row->customer_id ?? null,
            'customer_name' => $row->customer?->company_name ?? __('Unknown'),
            'revenue' => $revenue,
            'profit' => $profit,
            'margin_percent' => $row->gross_margin_percent !== null ? (float) $row->gross_margin_percent : ($revenue > 0 ? round(($profit / $revenue) * 100, 3) : null),
            'profit_per_job' => $jobs > 0 ? round($profit / $jobs, 2) : null,
            'jobs_completed' => $jobs,
        ];
    }
}
