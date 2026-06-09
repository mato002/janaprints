<?php

namespace App\Support\Production;

use App\Models\Production\JobCostSheet;
use Illuminate\Support\Collection;

class CustomerProfitabilityService
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
            ->with('jobCard.customer')
            ->get();

        return $sheets
            ->groupBy(fn (JobCostSheet $sheet) => $sheet->jobCard?->customer_id)
            ->map(function (Collection $group, $customerId) {
                $customer = $group->first()->jobCard?->customer;
                $revenue = (float) $group->sum('revenue');
                $cost = (float) $group->sum('total_cost');
                $profit = $revenue - $cost;
                $jobCount = $group->count();

                return [
                    'customer_id' => $customerId,
                    'customer_name' => $customer?->customer_name ?? __('Unknown'),
                    'revenue' => round($revenue, 2),
                    'total_cost' => round($cost, 2),
                    'profit' => round($profit, 2),
                    'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                    'jobs_count' => $jobCount,
                    'average_job_value' => $jobCount > 0 ? round($revenue / $jobCount, 2) : 0,
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

        if (! empty($filters['customer_id'])) {
            $query->whereHas('jobCard', fn ($q) => $q->where('customer_id', $filters['customer_id']));
        }

        return $query;
    }
}
