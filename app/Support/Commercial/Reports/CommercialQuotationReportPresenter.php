<?php

namespace App\Support\Commercial\Reports;

use App\Enums\QuotationStatus;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class CommercialQuotationReportPresenter
{
    public function __construct(
        protected CommercialQuotationReportScopeResolver $scopeResolver,
        protected CommercialQuotationReportQueries $queries,
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
            'title' => __('Quotation Reports'),
            'description' => __('Commercial quotation pipeline, value, aging, and win-rate analytics from operational quote data.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'customers' => $resolved['customers'],
            'salespersons' => $resolved['salespersons'],
            'can_export' => $resolved['can_export'],
            'readiness' => $resolved['readiness'],
            'report_ready' => $resolved['report_ready'],
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
            ['key' => 'summary', 'label' => __('Quotation Summary')],
            ['key' => 'open', 'label' => __('Open Quotations')],
            ['key' => 'expired', 'label' => __('Expired Quotations')],
            ['key' => 'accepted', 'label' => __('Accepted Quotations')],
            ['key' => 'rejected', 'label' => __('Rejected Quotations')],
            ['key' => 'value_analysis', 'label' => __('Quotation Value Analysis')],
            ['key' => 'aging', 'label' => __('Quotation Aging')],
            ['key' => 'win_rate', 'label' => __('Quote Win Rate')],
            ['key' => 'by_customer', 'label' => __('Quotation By Customer')],
            ['key' => 'by_salesperson', 'label' => __('Quotation By Salesperson')],
            ['key' => 'by_branch', 'label' => __('Quotation By Branch')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(CommercialQuotationReportScope $scope): array
    {
        return $this->cache->remember(
            'dashboard',
            "commercial-quotation-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            (int) config('platform.cache.dashboard', 60),
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(CommercialQuotationReportScope $scope): array
    {
        return [
            ['label' => __('Quotes Issued'), 'value' => (string) $this->queries->countIssued($scope), 'icon' => 'document-text'],
            ['label' => __('Quotes Accepted'), 'value' => (string) $this->queries->countByStatus($scope, [QuotationStatus::Accepted, QuotationStatus::Converted]), 'icon' => 'check-circle'],
            ['label' => __('Total Quote Value'), 'value' => $this->queries->money($this->queries->sumTotalValue($scope)), 'icon' => 'currency-dollar'],
            ['label' => __('Conversion %'), 'value' => $this->queries->conversionPercent($scope).'%', 'icon' => 'chart-pie'],
            ['label' => __('Average Quote Value'), 'value' => $this->queries->money($this->queries->averageQuoteValue($scope)), 'icon' => 'chart-bar'],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Quotes Issued'), 'document-text'],
            [__('Quotes Accepted'), 'check-circle'],
            [__('Total Quote Value'), 'currency-dollar'],
            [__('Conversion %'), 'chart-pie'],
            [__('Average Quote Value'), 'chart-bar'],
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
    protected function presentTab(CommercialQuotationReportScope $scope): array
    {
        if (! $this->queries->hasTable('quotations')) {
            return ['type' => 'placeholder', 'message' => __('Quotation data is not available yet.')];
        }

        return match ($scope->tab) {
            'open' => $this->quoteListTab(__('Open Quotations'), $this->queries->paginateQuotations($scope, 'open')),
            'expired' => $this->quoteListTab(__('Expired Quotations'), $this->queries->paginateQuotations($scope, 'expired')),
            'accepted' => $this->quoteListTab(__('Accepted Quotations'), $this->queries->paginateQuotations($scope, [QuotationStatus::Accepted->value, QuotationStatus::Converted->value])),
            'rejected' => $this->quoteListTab(__('Rejected Quotations'), $this->queries->paginateQuotations($scope, QuotationStatus::Rejected)),
            'value_analysis' => [
                'type' => 'table',
                'columns' => [__('Value Band'), __('Quotes'), __('Total Value'), __('Average Value')],
                'rows' => $this->queries->valueAnalysis($scope),
                'paginator' => null,
            ],
            'aging' => [
                'type' => 'table',
                'columns' => [__('Age Band'), __('Open Quotes'), __('Value')],
                'rows' => $this->queries->agingBuckets($scope),
                'paginator' => null,
            ],
            'win_rate' => [
                'type' => 'win_rate',
                'data' => $this->queries->winRateAnalysis($scope),
            ],
            'by_customer' => $this->groupedTableTab(
                [__('Customer'), __('Quotes'), __('Total Value'), __('Average Value'), __('Win Rate')],
                $this->queries->paginateByCustomer($scope),
            ),
            'by_salesperson' => $this->groupedTableTab(
                [__('Salesperson'), __('Quotes'), __('Total Value'), __('Average Value'), __('Win Rate')],
                $this->queries->paginateBySalesperson($scope),
            ),
            'by_branch' => [
                'type' => 'table',
                'columns' => [__('Branch'), __('Quotes'), __('Total Value'), __('Won'), __('Win Rate')],
                'rows' => $this->queries->branchBreakdown($scope),
                'paginator' => null,
            ],
            default => [
                'type' => 'summary',
                'tables' => [
                    [
                        'title' => __('Quotation By Branch'),
                        'columns' => [__('Branch'), __('Quotes'), __('Total Value'), __('Won'), __('Win Rate')],
                        'rows' => $this->queries->branchBreakdown($scope),
                    ],
                    [
                        'title' => __('Quotation By Salesperson'),
                        'columns' => [__('Salesperson'), __('Quotes'), __('Total Value'), __('Average Value'), __('Win Rate')],
                        'rows' => collect($this->queries->paginateBySalesperson($scope)->items())->values()->all(),
                    ],
                ],
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function quoteListTab(string $title, \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return [
            'type' => 'table',
            'title' => $title,
            'columns' => [__('Quote'), __('Customer'), __('Date'), __('Valid Until'), __('Value'), __('Status'), __('Salesperson')],
            'rows' => $paginator,
            'paginator' => $paginator,
        ];
    }

    /**
     * @param  list<string>  $columns
     * @return array<string, mixed>
     */
    protected function groupedTableTab(array $columns, \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return [
            'type' => 'table',
            'columns' => $columns,
            'rows' => $paginator,
            'paginator' => $paginator,
        ];
    }
}
