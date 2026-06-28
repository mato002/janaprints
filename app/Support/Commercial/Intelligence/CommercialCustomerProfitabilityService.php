<?php

namespace App\Support\Commercial\Intelligence;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\SalesOrder;
use App\Support\EnumLabel;
use App\Support\Production\CustomerProfitabilityService;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Support\Facades\DB;

class CommercialCustomerProfitabilityService
{
    public function __construct(
        protected CommercialIntelligenceQuery $query,
        protected CustomerProfitabilityService $productionProfitability,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function profile(Customer $customer): array
    {
        $companyId = (int) $customer->company_id;

        $ordersCount = (int) SalesOrder::query()
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->count();

        $revenue = (float) CustomerInvoice::query()
            ->where('customer_id', $customer->id)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('invoice_type', '!=', CustomerInvoiceType::CreditNote)
            ->sum('total_amount');

        $payments = (float) CustomerPayment::query()
            ->where('customer_id', $customer->id)
            ->where('status', CustomerPaymentStatus::Posted)
            ->sum('amount');

        $profitRow = collect($this->productionProfitability->aggregate($companyId, $customer->branch_id, [
            'customer_id' => $customer->id,
        ]))->first();

        $estimatedProfit = (float) ($profitRow['profit'] ?? 0);
        $marginPercent = (float) ($profitRow['margin_percent'] ?? 0);
        $jobsCount = (int) ($profitRow['jobs_count'] ?? 0);

        return [
            'total_orders' => $ordersCount,
            'total_revenue' => round($revenue, 2),
            'total_payments' => round($payments, 2),
            'average_job_value' => $jobsCount > 0 ? round($revenue / max(1, $ordersCount), 2) : 0,
            'estimated_profit' => round($estimatedProfit, 2),
            'estimated_margin_percent' => $marginPercent,
            'jobs_count' => $jobsCount,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function topCustomers(IntelligenceScope $scope, int $limit = 10): array
    {
        return array_slice(
            $this->productionProfitability->ranking($scope->companyId, $scope->branchId, [
                'date_from' => $scope->fromDate,
                'date_to' => $scope->toDate,
            ], $limit),
            0,
            $limit,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mostActiveCustomers(IntelligenceScope $scope, int $limit = 10): array
    {
        $query = SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled]);

        if ($scope->branchId) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->fromDate) {
            $query->whereDate('order_date', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('order_date', '<=', $scope->toDate);
        }

        return $query
            ->select('customer_id', DB::raw('COUNT(*) as order_count'), DB::raw('COALESCE(SUM(total_amount), 0) as revenue'))
            ->groupBy('customer_id')
            ->orderByDesc('order_count')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $customer = Customer::query()->find($row->customer_id);

                return [
                    'customer_id' => $row->customer_id,
                    'customer_name' => $customer?->company_name ?? __('Unknown'),
                    'order_count' => (int) $row->order_count,
                    'revenue' => round((float) $row->revenue, 2),
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentJobs(Customer $customer, int $limit = 5): array
    {
        return ProductionJobCard::query()
            ->where('customer_id', $customer->id)
            ->with(['costSheet', 'salesOrder'])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (ProductionJobCard $job) => [
                'id' => $job->id,
                'job_number' => $job->job_card_number,
                'status' => EnumLabel::of($job->status),
                'revenue' => round((float) ($job->costSheet?->revenue ?? $job->salesOrder?->total_amount ?? 0), 2),
                'estimated_profit' => round((float) ($job->costSheet?->gross_profit ?? 0), 2),
                'estimated_margin_percent' => round((float) ($job->costSheet?->gross_margin_percent ?? 0), 2),
            ])
            ->all();
    }
}
