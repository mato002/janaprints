<?php

namespace App\Support\Production;

use App\Enums\ProductionType;
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
     * @return array<int, array<string, mixed>>
     */
    public static function productProfitability(int $companyId, ?int $branchId = null): array
    {
        $sheets = JobCostSheet::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('jobCard')
            ->get();

        return $sheets
            ->groupBy(fn ($s) => $s->jobCard?->production_type?->value ?? 'unknown')
            ->map(function ($group, $type) {
                $revenue = $group->sum('revenue');
                $cost = $group->sum('total_cost');
                $profit = $revenue - $cost;

                return [
                    'production_type' => $type,
                    'label' => str(ProductionType::tryFrom($type)?->value ?? $type)->headline(),
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function branchProfitability(int $companyId): array
    {
        $sheets = JobCostSheet::query()
            ->where('company_id', $companyId)
            ->with('jobCard.branch')
            ->get();

        return $sheets
            ->groupBy('branch_id')
            ->map(function ($group, $branchId) {
                $branch = $group->first()->jobCard?->branch;
                $revenue = $group->sum('revenue');
                $cost = $group->sum('total_cost');
                $profit = $revenue - $cost;

                return [
                    'branch_id' => $branchId,
                    'branch_name' => $branch?->name ?? __('Unknown'),
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

    /**
     * Material cost and margin grouped by inventory category (paper, ink, banner, etc.).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function materialCategoryProfitability(int $companyId, ?int $branchId = null): array
    {
        $sheets = JobCostSheet::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['lines.inventoryItem.category', 'jobCard.salesOrder'])
            ->get();

        $byCategory = [];

        foreach ($sheets as $sheet) {
            $revenueShare = $sheet->lines->where('cost_category', \App\Enums\JobCostCategory::Material)->isNotEmpty()
                ? ($sheet->revenue / max(1, $sheet->lines->count()))
                : 0;

            foreach ($sheet->lines as $line) {
                if ($line->cost_category !== \App\Enums\JobCostCategory::Material) {
                    continue;
                }

                $catId = $line->inventoryItem?->inventory_category_id ?? 0;
                $catName = $line->inventoryItem?->category?->name ?? __('Materials');

                if (! isset($byCategory[$catId])) {
                    $byCategory[$catId] = [
                        'category_name' => $catName,
                        'cost' => 0.0,
                        'revenue' => 0.0,
                        'line_count' => 0,
                    ];
                }

                $byCategory[$catId]['cost'] += (float) $line->line_total;
                $byCategory[$catId]['revenue'] += $revenueShare;
                $byCategory[$catId]['line_count']++;
            }
        }

        return collect($byCategory)
            ->map(function ($row) {
                $profit = $row['revenue'] - $row['cost'];

                return [
                    'category_name' => $row['category_name'],
                    'revenue' => round($row['revenue'], 2),
                    'cost' => round($row['cost'], 2),
                    'profit' => round($profit, 2),
                    'margin_percent' => $row['revenue'] > 0 ? round(($profit / $row['revenue']) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('cost')
            ->values()
            ->all();
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
            'product_profitability' => self::productProfitability($companyId, $branchId),
            'material_category_profitability' => self::materialCategoryProfitability($companyId, $branchId),
            'branch_profitability' => self::branchProfitability($companyId),
        ];
    }
}
