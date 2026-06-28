<?php

namespace App\Support\Commercial\Intelligence;

use App\Enums\FulfilmentStatus;
use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionJobCard;
use App\Support\Reports\IntelligenceAggregateQueries;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Support\Facades\Schema;

class CommercialExecutiveDashboardService
{
    public function __construct(
        protected IntelligenceAggregateQueries $aggregates,
        protected CommercialJobProfitabilityService $jobs,
        protected CommercialCustomerProfitabilityService $customers,
        protected CommercialProductProfitabilityService $products,
        protected CommercialWasteIntelligenceService $waste,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function widgets(IntelligenceScope $scope): array
    {
        $revenue = $this->aggregates->sumRevenueMtd($scope);
        $collections = $this->sumCollections($scope);
        $receivables = $this->aggregates->sumReceivables($scope);
        $wasteSummary = $this->waste->summary($scope);

        return [
            'kpis' => [
                ['label' => __('Revenue'), 'value' => $revenue !== null ? $this->aggregates->money($revenue) : '—', 'icon' => 'chart-pie'],
                ['label' => __('Collections'), 'value' => $collections !== null ? $this->aggregates->money($collections) : '—', 'icon' => 'cash'],
                ['label' => __('Outstanding receivables'), 'value' => $receivables !== null ? $this->aggregates->money($receivables) : '—', 'icon' => 'receipt-tax'],
                ['label' => __('Jobs in production'), 'value' => (string) $this->countJobsInProduction($scope), 'icon' => 'cog'],
                ['label' => __('Jobs awaiting collection'), 'value' => (string) $this->countJobsAwaitingCollection($scope), 'icon' => 'inbox'],
                ['label' => __('Waste cost'), 'value' => $this->aggregates->money($wasteSummary['waste_cost']), 'icon' => 'exclamation'],
            ],
            'top_customers' => $this->customers->topCustomers($scope, 5),
            'top_products' => $this->products->byProductName($scope, 5),
            'waste' => $wasteSummary,
        ];
    }

    protected function sumCollections(IntelligenceScope $scope): ?float
    {
        if (! Schema::hasTable('customer_payments')) {
            return null;
        }

        return (float) \App\Models\Sales\CustomerPayment::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', \App\Enums\CustomerPaymentStatus::Posted)
            ->whereDate('payment_date', '>=', $scope->fromDate)
            ->whereDate('payment_date', '<=', $scope->toDate)
            ->sum('amount');
    }

    protected function countJobsInProduction(IntelligenceScope $scope): int
    {
        if (! Schema::hasTable('production_job_cards')) {
            return 0;
        }

        return (int) ProductionJobCard::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereIn('status', [
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::QualityCheck,
                ProductionJobCardStatus::Rework,
                ProductionJobCardStatus::Outsourced,
            ])
            ->count();
    }

    protected function countJobsAwaitingCollection(IntelligenceScope $scope): int
    {
        if (! Schema::hasTable('production_fulfilments')) {
            return (int) ProductionJobCard::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->where('status', ProductionJobCardStatus::ReadyForDispatch)
                ->count();
        }

        return (int) ProductionFulfilment::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', FulfilmentStatus::ReadyForCollection)
            ->count();
    }
}
