<?php

namespace App\Support\Production;

use App\Models\Production\JobCostSheet;
use Illuminate\Support\Collection;

class SalespersonProfitabilityService
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
            ->with(['jobCard.salesOrder.creator', 'jobCard'])
            ->get();

        return $sheets
            ->groupBy(fn (JobCostSheet $sheet) => $sheet->jobCard?->salesOrder?->created_by)
            ->map(function (Collection $group, $userId) {
                $user = $group->first()->jobCard?->salesOrder?->creator;
                $revenue = (float) $group->sum('revenue');
                $cost = (float) $group->sum('total_cost');
                $profit = $revenue - $cost;

                $jobsWon = $group->count();
                $jobsDelivered = $group->filter(fn (JobCostSheet $sheet) => in_array(
                    $sheet->jobCard?->status?->value,
                    ['completed', 'ready_for_dispatch'],
                    true,
                ))->count();

                return [
                    'salesperson_id' => $userId,
                    'salesperson_name' => $user?->name ?? __('Unassigned'),
                    'revenue' => round($revenue, 2),
                    'total_cost' => round($cost, 2),
                    'profit' => round($profit, 2),
                    'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                    'jobs_won' => $jobsWon,
                    'jobs_delivered' => $jobsDelivered,
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

        if (! empty($filters['date_from'])) {
            $query->whereDate('calculated_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('calculated_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
