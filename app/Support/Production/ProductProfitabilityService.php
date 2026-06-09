<?php

namespace App\Support\Production;

use App\Enums\ProductionType;
use App\Models\Production\JobCostSheet;
use Illuminate\Support\Collection;

class ProductProfitabilityService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function ranking(int $companyId, ?int $branchId = null, array $filters = [], int $limit = 20): array
    {
        return collect($this->aggregate($companyId, $branchId, $filters))
            ->sortByDesc('profit')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function aggregate(int $companyId, ?int $branchId = null, array $filters = []): array
    {
        $sheets = $this->query($companyId, $branchId, $filters)
            ->with(['jobCard.salesOrder.items'])
            ->get();

        return $sheets
            ->groupBy(fn (JobCostSheet $sheet) => $sheet->jobCard?->production_type?->value ?? 'unknown')
            ->map(function (Collection $group, string $type) {
                $revenue = (float) $group->sum('revenue');
                $cost = (float) $group->sum('total_cost');
                $profit = $revenue - $cost;
                $unitsSold = $group->sum(fn (JobCostSheet $sheet) => (float) ($sheet->jobCard?->salesOrder?->items?->sum('quantity') ?? 0));

                return [
                    'production_type' => $type,
                    'label' => str(ProductionType::tryFrom($type)?->value ?? $type)->headline(),
                    'revenue' => round($revenue, 2),
                    'total_cost' => round($cost, 2),
                    'profit' => round($profit, 2),
                    'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                    'units_sold' => round($unitsSold, 3),
                    'jobs_count' => $group->count(),
                ];
            })
            ->sortByDesc('profit')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function query(int $companyId, ?int $branchId, array $filters)
    {
        $query = JobCostSheet::query()->where('company_id', $companyId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if (! empty($filters['production_type'])) {
            $query->whereHas('jobCard', fn ($q) => $q->where('production_type', $filters['production_type']));
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('calculated_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('calculated_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
