<?php

namespace App\Support\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\PublicQuoteRequestStatus;
use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Support\Commercial\Reports\CommercialArtworkReportQueries;
use App\Support\Commercial\Reports\CommercialArtworkReportScope;
use App\Support\Commercial\Reports\CommercialQuotationReportQueries;
use App\Support\Commercial\Reports\CommercialQuotationReportScope;
use App\Support\Commercial\Reports\CommercialSalesReportQueries;
use App\Support\Commercial\Reports\CommercialSalesReportScope;
use App\Support\Reports\IntelligenceAggregateQueries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class CommercialCeoSalesIntelligencePresenter
{
    private const TOP_LIMIT = 5;

    public function __construct(
        protected CommercialSalesReportQueries $salesQueries,
        protected CommercialQuotationReportQueries $quotationQueries,
        protected CommercialArtworkReportQueries $artworkQueries,
        protected IntelligenceAggregateQueries $intelligence,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function build(): ?array
    {
        $user = auth()->user();

        if (! $user?->can('quotations.view')
            && ! $user?->can('sales_orders.view')
            && ! $user?->can('artwork.view')
            && ! $user?->can('public_leads.quote_requests.view')) {
            return null;
        }

        $salesScope = $this->salesScope();
        $quotationScope = $this->quotationScope();
        $artworkScope = $this->artworkScope();

        $quoteConversion = $this->quoteConversion($quotationScope);
        $salesPerformance = $this->salesPerformance($salesScope);
        $lostBusiness = $this->lostBusiness($salesScope, $quotationScope);
        $artworkImpact = $this->artworkImpact($artworkScope);
        $productionReadiness = $this->productionReadiness();
        $executiveSummary = $this->executiveSummary(
            $quoteConversion,
            $salesScope,
            $productionReadiness,
            $artworkImpact,
        );

        return [
            'quote_conversion' => $quoteConversion,
            'sales_performance' => $salesPerformance,
            'lost_business' => $lostBusiness,
            'artwork_impact' => $artworkImpact,
            'production_readiness' => $productionReadiness,
            'executive_summary' => $executiveSummary,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function quoteConversion(CommercialQuotationReportScope $scope): array
    {
        if (! auth()->user()?->can('quotations.view') && ! auth()->user()?->can('public_leads.quote_requests.view')) {
            return [];
        }

        $quoteRequests = 0;
        if (auth()->user()?->can('public_leads.quote_requests.view') && Schema::hasTable('public_quote_requests')) {
            $quoteRequests = (int) PublicQuoteRequest::query()
                ->where('status', '!=', PublicQuoteRequestStatus::Spam)
                ->whereDate('created_at', '>=', $scope->fromDate)
                ->whereDate('created_at', '<=', $scope->toDate)
                ->count();
        }

        $quotations = 0;
        $accepted = 0;
        $rejected = 0;
        $converted = 0;
        $conversionRate = 0.0;

        if (auth()->user()?->can('quotations.view')) {
            $quotations = (int) Quotation::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereDate('quotation_date', '>=', $scope->fromDate)
                ->whereDate('quotation_date', '<=', $scope->toDate)
                ->count();

            $accepted = $this->quotationQueries->countByStatus($scope, [
                QuotationStatus::Accepted,
                QuotationStatus::Converted,
            ]);
            $rejected = $this->quotationQueries->countByStatus($scope, QuotationStatus::Rejected);
            $converted = $this->quotationQueries->countByStatus($scope, QuotationStatus::Converted);
            $conversionRate = $quotations > 0
                ? round(($converted / $quotations) * 100, 1)
                : 0.0;
        }

        $funnel = array_values(array_filter([
            auth()->user()?->can('public_leads.quote_requests.view') ? [
                'key' => 'quote_requests',
                'label' => __('Quote Requests'),
                'count' => $quoteRequests,
            ] : null,
            auth()->user()?->can('quotations.view') ? [
                'key' => 'quotations',
                'label' => __('Quotations'),
                'count' => $quotations,
            ] : null,
            auth()->user()?->can('quotations.view') ? [
                'key' => 'accepted',
                'label' => __('Accepted'),
                'count' => $accepted,
            ] : null,
            auth()->user()?->can('quotations.view') ? [
                'key' => 'rejected',
                'label' => __('Rejected'),
                'count' => $rejected,
            ] : null,
            auth()->user()?->can('quotations.view') ? [
                'key' => 'converted',
                'label' => __('Converted'),
                'count' => $converted,
            ] : null,
        ]));

        return [
            'funnel' => $funnel,
            'conversion_rate' => $conversionRate,
            'conversion_label' => $conversionRate.'%',
            'win_rate' => auth()->user()?->can('quotations.view')
                ? $this->quotationQueries->conversionPercent($scope)
                : null,
            'href' => Route::has('commercial.reports.conversion.index')
                ? route('commercial.reports.conversion.index')
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function salesPerformance(CommercialSalesReportScope $scope): array
    {
        if (! auth()->user()?->can('sales_orders.view') && ! auth()->user()?->can('crm.customers.view')) {
            return [];
        }

        $topStaff = [];
        if (auth()->user()?->can('sales_orders.view')) {
            $topStaff = collect($this->salesQueries->salespersonBreakdown($scope))
                ->take(self::TOP_LIMIT)
                ->values()
                ->all();
        }

        $topCustomers = [];
        if (auth()->user()?->can('sales_orders.view') || auth()->user()?->can('crm.customers.view')) {
            $topCustomers = $this->salesQueries->topCustomers($scope)
                ->take(self::TOP_LIMIT)
                ->values()
                ->all();
        }

        return [
            'top_staff' => $topStaff,
            'top_customers' => $topCustomers,
            'top_products' => auth()->user()?->can('sales_orders.view') ? $this->topProducts($scope) : [],
            'top_categories' => auth()->user()?->can('quotations.view') ? $this->topCategories($scope) : [],
            'href' => Route::has('commercial.reports.sales.index')
                ? route('commercial.reports.sales.index')
                : null,
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public function topProducts(CommercialSalesReportScope $scope): array
    {
        if (! Schema::hasTable('sales_order_items') || ! Schema::hasTable('sales_orders')) {
            return [];
        }

        $rows = SalesOrderItem::query()
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->where('sales_orders.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
            ->whereDate('sales_orders.order_date', '>=', $scope->fromDate)
            ->whereDate('sales_orders.order_date', '<=', $scope->toDate)
            ->whereNotIn('sales_orders.status', $this->salesQueries->revenueExcludedStatuses())
            ->select(
                'sales_order_items.item_name',
                DB::raw('SUM(sales_order_items.line_total) as revenue'),
                DB::raw('SUM(sales_order_items.quantity) as quantity'),
            )
            ->groupBy('sales_order_items.item_name')
            ->orderByDesc('revenue')
            ->limit(self::TOP_LIMIT)
            ->get();

        return $rows->map(fn ($row) => [
            'name' => $row->item_name ?: '—',
            'revenue' => $this->money((float) $row->revenue),
            'quantity' => number_format((float) $row->quantity, 0),
        ])->all();
    }

    /**
     * @return list<array<string, string>>
     */
    public function topCategories(CommercialSalesReportScope $scope): array
    {
        if (! Schema::hasTable('quotation_items') || ! Schema::hasTable('quotations')) {
            return [];
        }

        $rows = QuotationItem::query()
            ->join('quotations', 'quotations.id', '=', 'quotation_items.quotation_id')
            ->where('quotations.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('quotations.branch_id', $scope->branchId))
            ->whereDate('quotations.quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotations.quotation_date', '<=', $scope->toDate)
            ->whereIn('quotations.status', [
                QuotationStatus::Accepted->value,
                QuotationStatus::Converted->value,
                QuotationStatus::Sent->value,
                QuotationStatus::Viewed->value,
            ])
            ->select(
                'quotation_items.item_type',
                DB::raw('SUM(quotation_items.line_total) as revenue'),
                DB::raw('COUNT(DISTINCT quotations.id) as quotes'),
            )
            ->groupBy('quotation_items.item_type')
            ->orderByDesc('revenue')
            ->limit(self::TOP_LIMIT)
            ->get();

        return $rows->map(function ($row) {
            $type = $row->item_type instanceof QuotationItemType
                ? $row->item_type->value
                : (string) $row->item_type;

            return [
                'name' => ucfirst(str_replace('_', ' ', $type)),
                'revenue' => $this->money((float) $row->revenue),
                'quotes' => (string) $row->quotes,
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function lostBusiness(CommercialSalesReportScope $scope, CommercialQuotationReportScope $quotationScope): array
    {
        $items = [];

        if (auth()->user()?->can('quotations.view')) {
            $rejectedCount = $this->quotationQueries->countByStatus($quotationScope, QuotationStatus::Rejected);
            $expiredCount = $this->quotationQueries->countExpired($quotationScope);

            $items[] = [
                'key' => 'rejected_quotes',
                'label' => __('Rejected Quotes'),
                'count' => $rejectedCount,
                'amount' => $this->money($this->quotationQueries->sumTotalValue($quotationScope, [QuotationStatus::Rejected->value])),
            ];
            $items[] = [
                'key' => 'expired_quotes',
                'label' => __('Expired Quotes'),
                'count' => $expiredCount,
                'amount' => null,
            ];
        }

        if (auth()->user()?->can('sales_orders.view')) {
            $cancelledCount = (int) $this->salesQueries->baseOrderQuery($scope)
                ->where('sales_orders.status', SalesOrderStatus::Cancelled)
                ->count();
            $cancelledValue = (float) $this->salesQueries->baseOrderQuery($scope)
                ->where('sales_orders.status', SalesOrderStatus::Cancelled)
                ->sum('sales_orders.total_amount');

            $items[] = [
                'key' => 'cancelled_orders',
                'label' => __('Cancelled Orders'),
                'count' => $cancelledCount,
                'amount' => $this->money($cancelledValue),
            ];
        }

        return [
            'summary' => $items,
            'href' => Route::has('commercial.reports.sales.index')
                ? route('commercial.reports.sales.index', ['tab' => 'lost'])
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function artworkImpact(CommercialArtworkReportScope $scope): array
    {
        if (! auth()->user()?->can('artwork.view')) {
            return [];
        }

        $delayed = $this->artworkQueries->delayedRequests($scope);
        $avgHours = $this->artworkQueries->averageApprovalTimeHours($scope);

        $ordersBlocked = 0;
        if (auth()->user()?->can('sales_orders.view') && Schema::hasTable('sales_orders')) {
            $ordersBlocked = (int) SalesOrder::query()->forTenant()
                ->where('status', SalesOrderStatus::Confirmed)
                ->where(function ($query) {
                    $query->whereDoesntHave('artworkRequest')
                        ->orWhereHas('artworkRequest', fn ($artwork) => $artwork->where('status', '!=', ArtworkRequestStatus::Approved));
                })
                ->count();
        }

        return [
            'delayed_jobs' => max($delayed, $ordersBlocked),
            'delayed_label' => (string) max($delayed, $ordersBlocked),
            'avg_approval_time' => $this->artworkQueries->formatHours($avgHours),
            'avg_approval_hours' => $avgHours,
            'href' => Route::has('commercial.reports.artwork.index')
                ? route('commercial.reports.artwork.index')
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productionReadiness(): array
    {
        if (! auth()->user()?->can('sales_orders.view') && ! auth()->user()?->can('quotations.view')) {
            return [];
        }

        $ready = 0;
        $waitingArtwork = 0;
        $waitingApproval = 0;

        if (auth()->user()?->can('sales_orders.view')) {
            $ready = (int) SalesOrder::query()->forTenant()
                ->where('status', SalesOrderStatus::ReadyForProduction)
                ->count();

            $waitingArtwork = (int) SalesOrder::query()->forTenant()
                ->where('status', SalesOrderStatus::Confirmed)
                ->where(function ($query) {
                    $query->whereDoesntHave('artworkRequest')
                        ->orWhereHas('artworkRequest', fn ($artwork) => $artwork->whereNotIn('status', [
                            ArtworkRequestStatus::Approved,
                        ]));
                })
                ->count();
        }

        if (auth()->user()?->can('quotations.view')) {
            $waitingApproval = (int) Quotation::query()->forTenant()
                ->where('status', QuotationStatus::PendingApproval)
                ->count();
        }

        if (auth()->user()?->can('artwork.view')) {
            $waitingApproval += (int) ArtworkRequest::query()->forTenant()
                ->whereIn('status', [
                    ArtworkRequestStatus::Submitted,
                    ArtworkRequestStatus::RevisionRequested,
                ])
                ->count();
        }

        return [
            'items' => array_values(array_filter([
                auth()->user()?->can('sales_orders.view') ? [
                    'key' => 'ready',
                    'label' => __('Orders Ready For Production'),
                    'count' => $ready,
                    'variant' => 'success',
                ] : null,
                auth()->user()?->can('sales_orders.view') ? [
                    'key' => 'waiting_artwork',
                    'label' => __('Orders Waiting On Artwork'),
                    'count' => $waitingArtwork,
                    'variant' => $waitingArtwork > 0 ? 'warning' : 'neutral',
                ] : null,
                (auth()->user()?->can('quotations.view') || auth()->user()?->can('artwork.view')) ? [
                    'key' => 'waiting_approval',
                    'label' => __('Orders Waiting On Approval'),
                    'count' => $waitingApproval,
                    'variant' => $waitingApproval > 0 ? 'warning' : 'neutral',
                ] : null,
            ])),
            'href' => Route::has('admin.sales-orders.dashboard')
                ? route('admin.sales-orders.dashboard')
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $quoteConversion
     * @param  array<string, mixed>  $productionReadiness
     * @param  array<string, mixed>  $artworkImpact
     * @return array<string, mixed>
     */
    public function executiveSummary(
        array $quoteConversion,
        CommercialSalesReportScope $salesScope,
        array $productionReadiness,
        array $artworkImpact,
    ): array {
        $conversionRate = (float) ($quoteConversion['conversion_rate'] ?? 0);
        $commercialHealth = $this->healthFromThresholds($conversionRate, 35, 15);

        $ready = 0;
        $blocked = 0;
        foreach ($productionReadiness['items'] ?? [] as $item) {
            if (($item['key'] ?? '') === 'ready') {
                $ready = (int) ($item['count'] ?? 0);
            }
            if (in_array($item['key'] ?? '', ['waiting_artwork', 'waiting_approval'], true)) {
                $blocked += (int) ($item['count'] ?? 0);
            }
        }
        $blocked += (int) ($artworkImpact['delayed_jobs'] ?? 0);
        $pipelineScore = $ready + $blocked > 0 ? ($ready / ($ready + $blocked)) * 100 : 100;
        $pipelineHealth = $this->healthFromThresholds($pipelineScore, 60, 35);

        $growth = auth()->user()?->can('sales_orders.view')
            ? $this->salesQueries->salesGrowthPercent($salesScope)
            : null;
        $revenueHealth = $growth === null
            ? $this->healthLevel('watch', __('Insufficient revenue history.'))
            : $this->healthFromThresholds($growth, 5, -5);

        $cashHealth = $this->cashCollectionHealth();

        return [
            'items' => [
                array_merge($commercialHealth, [
                    'key' => 'commercial',
                    'label' => __('Commercial Health'),
                    'summary' => __(':rate quote-to-order conversion this month with :converted of :quotations quotations converted.', [
                        'rate' => ($quoteConversion['conversion_label'] ?? '0%'),
                        'converted' => collect($quoteConversion['funnel'] ?? [])->firstWhere('key', 'converted')['count'] ?? 0,
                        'quotations' => collect($quoteConversion['funnel'] ?? [])->firstWhere('key', 'quotations')['count'] ?? 0,
                    ]),
                ]),
                array_merge($pipelineHealth, [
                    'key' => 'pipeline',
                    'label' => __('Pipeline Health'),
                    'summary' => __(':ready orders production-ready; :blocked blocked on artwork or approvals.', [
                        'ready' => $ready,
                        'blocked' => $blocked,
                    ]),
                ]),
                array_merge($revenueHealth, [
                    'key' => 'revenue',
                    'label' => __('Revenue Health'),
                    'summary' => $growth === null
                        ? __('Revenue trend unavailable for the selected period.')
                        : __('Sales revenue is :direction :percent% versus the prior period.', [
                            'direction' => $growth >= 0 ? __('up') : __('down'),
                            'percent' => number_format(abs($growth), 1),
                        ]),
                ]),
                array_merge($cashHealth, [
                    'key' => 'cash',
                    'label' => __('Cash Collection Health'),
                ]),
            ],
        ];
    }

    /**
     * @return array{status: string, status_label: string, variant: string}
     */
    protected function cashCollectionHealth(): array
    {
        if (! auth()->user()?->can('invoices.view') || ! Schema::hasTable('customer_invoices')) {
            return $this->healthLevel('watch', __('Invoice visibility required for collection health.'));
        }

        $outstanding = (float) CustomerInvoice::query()->forTenant()
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->where('balance_due', '>', 0)
            ->sum('balance_due');

        if ($outstanding <= 0) {
            return $this->healthLevel('healthy', __('No outstanding receivables on record.'));
        }

        $overdue = (float) CustomerInvoice::query()->forTenant()
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->where('balance_due', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->sum('balance_due');

        $overdueRatio = ($overdue / $outstanding) * 100;
        $health = $this->healthFromThresholds(100 - $overdueRatio, 75, 50);

        return array_merge($health, [
            'summary' => __(':overdue overdue of :outstanding outstanding receivables (:percent% overdue).', [
                'overdue' => $this->money($overdue),
                'outstanding' => $this->money($outstanding),
                'percent' => number_format($overdueRatio, 1),
            ]),
        ]);
    }

    /**
     * @return array{status: string, status_label: string, variant: string}
     */
    protected function healthFromThresholds(float $value, float $healthyMin, float $watchMin): array
    {
        if ($value >= $healthyMin) {
            return $this->healthLevel('healthy');
        }

        if ($value >= $watchMin) {
            return $this->healthLevel('watch');
        }

        return $this->healthLevel('at_risk');
    }

    /**
     * @return array{status: string, status_label: string, variant: string, summary?: string}
     */
    protected function healthLevel(string $status, ?string $summary = null): array
    {
        $map = [
            'healthy' => ['label' => __('Healthy'), 'variant' => 'success'],
            'watch' => ['label' => __('Watch'), 'variant' => 'warning'],
            'at_risk' => ['label' => __('At Risk'), 'variant' => 'danger'],
        ];

        $meta = $map[$status] ?? $map['watch'];

        $result = [
            'status' => $status,
            'status_label' => $meta['label'],
            'variant' => $meta['variant'],
        ];

        if ($summary !== null) {
            $result['summary'] = $summary;
        }

        return $result;
    }

    protected function salesScope(): CommercialSalesReportScope
    {
        return new CommercialSalesReportScope(
            companyId: (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            branchId: tenant()->branchId(),
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
            topLimit: self::TOP_LIMIT,
        );
    }

    protected function quotationScope(): CommercialQuotationReportScope
    {
        return new CommercialQuotationReportScope(
            companyId: (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            branchId: tenant()->branchId(),
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );
    }

    protected function artworkScope(): CommercialArtworkReportScope
    {
        return new CommercialArtworkReportScope(
            companyId: (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            branchId: tenant()->branchId(),
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );
    }

    protected function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }
}
