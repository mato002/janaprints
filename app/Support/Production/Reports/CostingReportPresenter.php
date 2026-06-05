<?php

namespace App\Support\Production\Reports;

use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class CostingReportPresenter
{
    public function __construct(
        protected CostingReportScopeResolver $scopeResolver,
        protected CostingReportQueries $queries,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];

        $kpis = $resolved['report_ready']
            ? $this->cachedKpis($scope)
            : $this->emptyKpis();

        return [
            'title' => __('Costing Reports'),
            'description' => __('Cost layers, margins, and job profitability from operational costing truth.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'customers' => $resolved['customers'],
            'job_cards' => $resolved['job_cards'],
            'production_types' => $resolved['production_types'],
            'can_export' => $resolved['can_export'],
            'readiness' => $resolved['readiness'],
            'report_ready' => $resolved['report_ready'],
            'kpis' => $kpis,
            'tabs' => $this->tabs(),
            'active_tab' => $scope->tab,
            'tab_data' => $this->presentTab($scope, $resolved['report_ready']),
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function tabs(): array
    {
        return [
            ['key' => 'job_profitability', 'label' => __('Job Profitability')],
            ['key' => 'product_cost', 'label' => __('Product Cost Analysis')],
            ['key' => 'paper_consumption', 'label' => __('Paper Consumption')],
            ['key' => 'ink_consumption', 'label' => __('Ink Consumption')],
            ['key' => 'production_cost_summary', 'label' => __('Production Cost Summary')],
            ['key' => 'customer_profitability', 'label' => __('Customer Profitability')],
            ['key' => 'monthly_margin', 'label' => __('Monthly Margin')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(CostingReportScope $scope): array
    {
        $ttl = (int) config('platform.cache.dashboard', 60);

        return $this->cache->remember(
            'dashboard',
            "costing-reports-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            $ttl,
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(CostingReportScope $scope): array
    {
        $totals = $this->queries->scopedTotals($scope);

        return [
            ['label' => __('Revenue'), 'value' => $this->queries->money($totals['revenue']), 'icon' => 'currency-dollar', 'hint' => null],
            ['label' => __('Total Cost'), 'value' => $this->queries->money($totals['total_cost']), 'icon' => 'cog', 'hint' => null],
            ['label' => __('Gross Profit'), 'value' => $this->queries->money($totals['gross_profit']), 'icon' => 'chart-pie', 'hint' => null],
            ['label' => __('Margin %'), 'value' => $this->queries->percent($totals['margin_percent']), 'icon' => 'chart-bar', 'hint' => null],
            ['label' => __('Jobs Costed'), 'value' => (string) $totals['job_count'], 'icon' => 'clipboard-list', 'hint' => null],
            ['label' => __('Paper Consumed'), 'value' => $this->queries->money($this->queries->categoryConsumptionValue($scope, 'PAPER')), 'icon' => 'document-text', 'hint' => null],
            ['label' => __('Ink Consumed'), 'value' => $this->queries->money($this->queries->categoryConsumptionValue($scope, 'INK')), 'icon' => 'color-swatch', 'hint' => null],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Revenue'), 'currency-dollar'],
            [__('Total Cost'), 'cog'],
            [__('Gross Profit'), 'chart-pie'],
            [__('Margin %'), 'chart-bar'],
            [__('Jobs Costed'), 'clipboard-list'],
            [__('Paper Consumed'), 'document-text'],
            [__('Ink Consumed'), 'color-swatch'],
        ];

        return collect($labels)->map(fn (array $item) => [
            'label' => $item[0],
            'value' => '—',
            'icon' => $item[1],
            'hint' => __('Awaiting costing data sources'),
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentTab(CostingReportScope $scope, bool $reportReady): array
    {
        if (! $reportReady) {
            return [
                'type' => 'placeholder',
                'message' => __('Job costing data is not available yet.'),
            ];
        }

        $columns = $this->columnsForTab($scope->tab);
        $rows = $this->queries->paginateForTab($scope);

        return [
            'type' => 'table',
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    /**
     * @return list<string>
     */
    public function columnsForTab(string $tab): array
    {
        return match ($tab) {
            'product_cost' => [__('Product'), __('Material Cost'), __('Labor Cost'), __('Machine Cost'), __('Overheads'), __('Total Cost')],
            'paper_consumption' => [__('Paper Type'), __('Consumed Qty'), __('Cost'), __('Waste %')],
            'ink_consumption' => [__('Ink Type'), __('Consumption'), __('Cost')],
            'production_cost_summary' => [__('Department'), __('Jobs'), __('Revenue'), __('Cost'), __('Profit')],
            'customer_profitability' => [__('Customer'), __('Revenue'), __('Cost'), __('Margin')],
            'monthly_margin' => [__('Month'), __('Revenue'), __('Cost'), __('Profit'), __('Margin %')],
            default => [__('Job Number'), __('Customer'), __('Revenue'), __('Material Cost'), __('Production Cost'), __('Outsourced Cost'), __('Total Cost'), __('Profit'), __('Margin %')],
        };
    }
}
