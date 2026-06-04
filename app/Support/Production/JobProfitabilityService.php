<?php

namespace App\Support\Production;

use App\Models\Production\JobCostSheet;
use App\Models\Production\JobProfitabilitySnapshot;
use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Collection;

class JobProfitabilityService
{
    /**
     * @return Collection<int, JobCostSheet>
     */
    public static function topProfitableJobs(int $companyId, ?int $branchId = null, int $limit = 10): Collection
    {
        return JobCostSheet::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('gross_profit', '>', 0)
            ->orderByDesc('gross_profit')
            ->limit($limit)
            ->with('jobCard.customer', 'jobCard.salesOrder')
            ->get();
    }

    /**
     * @return Collection<int, JobCostSheet>
     */
    public static function lossMakingJobs(int $companyId, ?int $branchId = null, int $limit = 10): Collection
    {
        return JobCostSheet::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('gross_profit', '<', 0)
            ->orderBy('gross_profit')
            ->limit($limit)
            ->with('jobCard.customer', 'jobCard.salesOrder')
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function customerProfitability(int $companyId, ?int $branchId = null): array
    {
        $sheets = JobCostSheet::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('jobCard.customer')
            ->get();

        return $sheets
            ->groupBy(fn ($s) => $s->jobCard?->customer_id)
            ->map(function ($group, $customerId) {
                $customer = $group->first()->jobCard?->customer;
                $revenue = $group->sum('revenue');
                $cost = $group->sum('total_cost');
                $profit = $revenue - $cost;

                return [
                    'customer_id' => $customerId,
                    'customer_name' => $customer?->customer_name ?? __('Unknown'),
                    'revenue' => round($revenue, 2),
                    'cost' => round($cost, 2),
                    'profit' => round($profit, 2),
                    'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                    'job_count' => $group->count(),
                ];
            })
            ->sortByDesc('profit')
            ->values()
            ->all();
    }

    public static function snapshotScope(
        int $companyId,
        string $scope,
        ?int $scopeId,
        string $label,
        ?string $periodStart = null,
        ?string $periodEnd = null,
    ): JobProfitabilitySnapshot {
        $query = JobCostSheet::query()->where('company_id', $companyId);

        if ($periodStart) {
            $query->where('calculated_at', '>=', $periodStart);
        }
        if ($periodEnd) {
            $query->where('calculated_at', '<=', $periodEnd);
        }

        $revenue = (float) $query->sum('revenue');
        $cost = (float) $query->sum('total_cost');
        $profit = $revenue - $cost;

        return JobProfitabilitySnapshot::query()->create([
            'company_id' => $companyId,
            'snapshot_scope' => $scope,
            'scope_id' => $scopeId,
            'scope_label' => $label,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'revenue' => $revenue,
            'total_cost' => $cost,
            'gross_profit' => $profit,
            'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
            'job_count' => $query->count(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function dashboard(int $companyId, ?int $branchId = null): array
    {
        return [
            'top_profitable' => self::topProfitableJobs($companyId, $branchId),
            'loss_making' => self::lossMakingJobs($companyId, $branchId),
            'customer_profitability' => self::customerProfitability($companyId, $branchId),
        ];
    }
}
