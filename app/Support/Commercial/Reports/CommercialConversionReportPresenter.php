<?php

namespace App\Support\Commercial\Reports;

use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class CommercialConversionReportPresenter
{
    public function __construct(
        protected CommercialConversionReportScopeResolver $scopeResolver,
        protected CommercialConversionReportQueries $queries,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];

        return [
            'title' => __('Conversion Reports'),
            'description' => __('Commercial funnel conversion from leads through quotes, orders, production, and delivery.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'lead_sources' => $resolved['lead_sources'],
            'salespersons' => $resolved['salespersons'],
            'can_export' => $resolved['can_export'],
            'readiness' => $resolved['readiness'],
            'report_ready' => $resolved['report_ready'],
            'has_production_pipeline' => $resolved['has_production_pipeline'],
            'has_dispatch_pipeline' => $resolved['has_dispatch_pipeline'],
            'kpis' => $resolved['report_ready'] ? $this->cachedKpis($scope) : $this->emptyKpis(),
            'tabs' => $this->tabs(),
            'active_tab' => $scope->tab,
            'tab_data' => $this->presentTab($scope),
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function tabs(): array
    {
        return [
            ['key' => 'full_funnel', 'label' => __('Full Commercial Funnel')],
            ['key' => 'lead_to_quote', 'label' => __('Lead → Quote')],
            ['key' => 'quote_to_order', 'label' => __('Quote → Order')],
            ['key' => 'order_to_production', 'label' => __('Order → Production')],
            ['key' => 'production_to_dispatch', 'label' => __('Production → Dispatch')],
            ['key' => 'dispatch_to_delivery', 'label' => __('Dispatch → Delivery')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(CommercialConversionReportScope $scope): array
    {
        return $this->cache->remember(
            'dashboard',
            "commercial-conversion-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            (int) config('platform.cache.dashboard', 60),
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(CommercialConversionReportScope $scope): array
    {
        return [
            ['label' => __('Lead-to-Quote %'), 'value' => $this->formatPct($this->queries->leadToQuotePercent($scope)), 'icon' => 'sparkles'],
            ['label' => __('Quote-to-Order %'), 'value' => $this->formatPct($this->queries->quoteToOrderPercent($scope)), 'icon' => 'document-text'],
            ['label' => __('Order-to-Production %'), 'value' => $this->formatPct($this->queries->orderToProductionPercent($scope)), 'icon' => 'cog'],
            ['label' => __('Production-to-Dispatch %'), 'value' => $this->formatPct($this->queries->productionToDispatchPercent($scope)), 'icon' => 'truck'],
            ['label' => __('Dispatch-to-Delivery %'), 'value' => $this->formatPct($this->queries->dispatchToDeliveryPercent($scope)), 'icon' => 'check-circle'],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Lead-to-Quote %'), 'sparkles'],
            [__('Quote-to-Order %'), 'document-text'],
            [__('Order-to-Production %'), 'cog'],
            [__('Production-to-Dispatch %'), 'truck'],
            [__('Dispatch-to-Delivery %'), 'check-circle'],
        ];

        return collect($labels)->map(fn (array $item) => [
            'label' => $item[0],
            'value' => '—',
            'icon' => $item[1],
            'hint' => __('Awaiting operational data sources'),
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentTab(CommercialConversionReportScope $scope): array
    {
        if (! $this->queries->hasTable('leads')) {
            return [
                'type' => 'placeholder',
                'message' => __('Lead and conversion data is not available yet.'),
            ];
        }

        $highlight = match ($scope->tab) {
            'lead_to_quote' => 'quotes',
            'quote_to_order' => 'orders',
            'order_to_production' => 'production',
            'production_to_dispatch' => 'dispatch',
            'dispatch_to_delivery' => 'delivered',
            default => null,
        };

        $focusLabel = match ($scope->tab) {
            'lead_to_quote' => __('Lead → Quote'),
            'quote_to_order' => __('Quote → Order'),
            'order_to_production' => __('Order → Production'),
            'production_to_dispatch' => __('Production → Dispatch'),
            'dispatch_to_delivery' => __('Dispatch → Delivery'),
            default => __('Full Commercial Funnel'),
        };

        return [
            'type' => 'funnel',
            'focus_label' => $focusLabel,
            'stages' => $this->queries->funnelStages($scope, $highlight),
            'drop_off' => $this->queries->dropOffTable($scope),
            'branch_rows' => $this->queries->branchConversionRows($scope),
            'salesperson_rows' => $this->queries->salespersonConversionRows($scope),
        ];
    }

    protected function formatPct(?float $value): string
    {
        return $value !== null ? $value.'%' : '—';
    }
}
