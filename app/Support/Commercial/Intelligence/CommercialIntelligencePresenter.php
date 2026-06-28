<?php

namespace App\Support\Commercial\Intelligence;

use App\Support\Reports\IntelligenceScope;
use App\Support\Reports\IntelligenceScopeResolver;
use Illuminate\Http\Request;

class CommercialIntelligencePresenter
{
    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
        protected CommercialJobProfitabilityService $jobs,
        protected CommercialCustomerProfitabilityService $customers,
        protected CommercialProductProfitabilityService $products,
        protected CommercialBranchProfitabilityService $branches,
        protected CommercialOutsourceProfitabilityService $outsource,
        protected CommercialWasteIntelligenceService $waste,
        protected CommercialExecutiveDashboardService $executive,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];
        $tab = (string) $request->query('tab', 'job_profitability');

        return [
            'title' => __('Commercial Intelligence'),
            'description' => __('Job, customer, product, branch profitability, outsource analysis, and waste intelligence.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'tab' => $tab,
            'tabs' => config('commercial_intelligence_reports.tabs', []),
            'data' => $this->tabData($tab, $scope),
            'executive' => $this->executive->widgets($scope),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function tabData(string $tab, IntelligenceScope $scope): array
    {
        return match ($tab) {
            'customer_profitability' => [
                'top_customers' => $this->customers->topCustomers($scope, 25),
                'most_active' => $this->customers->mostActiveCustomers($scope, 15),
            ],
            'product_profitability' => [
                'by_type' => $this->products->byProductionType($scope, 25),
                'by_product' => $this->products->byProductName($scope, 25),
                'rankings' => $this->products->rankings($scope, 5),
            ],
            'branch_profitability' => [
                'branches' => $this->branches->aggregate($scope),
            ],
            'outsource_profitability' => [
                'jobs' => $this->outsource->byJob($scope, 25),
                'vendors' => $this->outsource->byVendor($scope, 15),
            ],
            'waste_intelligence' => $this->waste->summary($scope),
            default => [
                'jobs' => $this->jobs->paginate($scope, 25),
            ],
        };
    }
}
