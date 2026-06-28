<?php

namespace App\Support\Commercial\Intelligence;

use App\Enums\CustomerPaymentStatus;
use App\Models\Branch;
use App\Models\Sales\CustomerPayment;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Support\Facades\DB;

class CommercialBranchProfitabilityService
{
    public function __construct(
        protected CommercialIntelligenceQuery $query,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function aggregate(IntelligenceScope $scope): array
    {
        $rows = $this->query->costSheets($scope)
            ->select([
                'branch_id',
                DB::raw('COALESCE(SUM(revenue), 0) as revenue'),
                DB::raw('COALESCE(SUM(total_cost), 0) as production_cost'),
                DB::raw('COALESCE(SUM(gross_profit), 0) as estimated_profit'),
                DB::raw('COUNT(*) as jobs_count'),
            ])
            ->groupBy('branch_id')
            ->get();

        $collections = $this->collectionsByBranch($scope);

        return $rows->map(function ($row) use ($collections) {
            $branch = Branch::query()->find($row->branch_id);
            $revenue = (float) $row->revenue;
            $collected = (float) ($collections[$row->branch_id] ?? 0);
            $profit = (float) $row->estimated_profit;

            return [
                'branch_id' => $row->branch_id,
                'branch_name' => $branch?->name ?? __('Unknown'),
                'revenue' => round($revenue, 2),
                'collections' => round($collected, 2),
                'production_cost' => round((float) $row->production_cost, 2),
                'estimated_profit' => round($profit, 2),
                'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                'jobs_count' => (int) $row->jobs_count,
            ];
        })
            ->sortByDesc('estimated_profit')
            ->values()
            ->all();
    }

    /**
     * @return array<int, float>
     */
    protected function collectionsByBranch(IntelligenceScope $scope): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('customer_payments')) {
            return [];
        }

        $query = CustomerPayment::query()
            ->where('company_id', $scope->companyId)
            ->where('status', CustomerPaymentStatus::Posted);

        if ($scope->branchId) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->fromDate) {
            $query->whereDate('payment_date', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('payment_date', '<=', $scope->toDate);
        }

        return $query
            ->select('branch_id', DB::raw('COALESCE(SUM(amount), 0) as collected'))
            ->groupBy('branch_id')
            ->pluck('collected', 'branch_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }
}
