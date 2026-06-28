<?php

namespace App\Support\Commercial\Intelligence;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Support\Facades\DB;

class CommercialOutsourceProfitabilityService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function byJob(IntelligenceScope $scope, int $limit = 25): array
    {
        $query = ProductionJobCard::query()
            ->where('company_id', $scope->companyId)
            ->whereNotNull('outsource_vendor_id')
            ->whereIn('status', [
                ProductionJobCardStatus::Outsourced,
                ProductionJobCardStatus::Returned,
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
            ])
            ->with(['customer', 'salesOrder', 'outsourceVendor', 'costSheet']);

        if ($scope->branchId) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->fromDate) {
            $query->whereDate('outsourced_at', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('outsourced_at', '<=', $scope->toDate);
        }

        return $query
            ->latest('outsourced_at')
            ->limit($limit)
            ->get()
            ->map(fn (ProductionJobCard $job) => $this->presentJob($job))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function byVendor(IntelligenceScope $scope, int $limit = 15): array
    {
        $query = ProductionJobCard::query()
            ->where('company_id', $scope->companyId)
            ->whereNotNull('outsource_vendor_id')
            ->with('costSheet');

        if ($scope->branchId) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->fromDate) {
            $query->whereDate('outsourced_at', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('outsourced_at', '<=', $scope->toDate);
        }

        return $query
            ->select([
                'outsource_vendor_id',
                DB::raw('COUNT(*) as job_count'),
                DB::raw('COALESCE(SUM(COALESCE(outsource_actual_cost, outsource_quoted_cost, 0)), 0) as vendor_cost'),
            ])
            ->groupBy('outsource_vendor_id')
            ->orderByDesc('vendor_cost')
            ->limit($limit)
            ->get()
            ->map(function ($row) use ($scope) {
                $jobs = ProductionJobCard::query()
                    ->where('company_id', $scope->companyId)
                    ->where('outsource_vendor_id', $row->outsource_vendor_id)
                    ->with('costSheet', 'outsourceVendor')
                    ->get();

                $revenue = (float) $jobs->sum(fn (ProductionJobCard $job) => (float) ($job->costSheet?->revenue ?? $job->salesOrder?->total_amount ?? 0));
                $vendorCost = (float) $row->vendor_cost;
                $profit = $revenue - $vendorCost;

                return [
                    'vendor_id' => $row->outsource_vendor_id,
                    'vendor_name' => $jobs->first()?->outsourceVendor?->name ?? __('Vendor'),
                    'job_count' => (int) $row->job_count,
                    'customer_revenue' => round($revenue, 2),
                    'vendor_cost' => round($vendorCost, 2),
                    'estimated_profit' => round($profit, 2),
                    'estimated_margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                ];
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentJob(ProductionJobCard $job): array
    {
        $revenue = (float) ($job->costSheet?->revenue ?? $job->salesOrder?->total_amount ?? 0);
        $vendorCost = (float) ($job->outsource_actual_cost ?? $job->outsource_quoted_cost ?? 0);
        $profit = $revenue - $vendorCost;

        return [
            'job_card_id' => $job->id,
            'job_number' => $job->job_card_number,
            'customer_name' => $job->customer?->company_name,
            'vendor_name' => $job->outsourceVendor?->name,
            'customer_revenue' => round($revenue, 2),
            'vendor_cost' => round($vendorCost, 2),
            'estimated_profit' => round($profit, 2),
            'estimated_margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
        ];
    }
}
