<?php

namespace App\Support\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerPaymentStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\PublicQuoteRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Support\Commercial\Reports\CommercialSalesReportQueries;
use App\Support\Commercial\Reports\CommercialSalesReportScope;
use App\Support\Reports\IntelligenceAggregateQueries;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class CommercialCommandCenterPresenter
{
    public function __construct(
        protected IntelligenceAggregateQueries $intelligence,
        protected CommercialSalesReportQueries $salesReportQueries,
        protected CommercialHandoffIntelligencePresenter $handoffIntelligence,
        protected CommercialRevenueReceivablesPresenter $revenueReceivables,
        protected CommercialCeoSalesIntelligencePresenter $ceoSalesIntelligence,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return [
            'kpis' => $this->kpiStrip(),
            'revenue_receivables' => $this->revenueReceivables->build(),
            'ceo_sales_intelligence' => $this->ceoSalesIntelligence->build(),
            'pipeline' => $this->pipeline(),
            'handoff_intelligence' => $this->handoffIntelligence->build(),
            'attention' => $this->attentionCenter(),
            'top_customers' => $this->topCustomers(),
            'profitability' => $this->profitabilityInsights(),
            'quick_actions' => $this->quickActions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function profitabilityInsights(): array
    {
        if (! auth()->user()?->can('reports.job_profitability.view')) {
            return ['available' => false];
        }

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return [
            'available' => true,
            'top_customers' => app(\App\Support\Production\CustomerProfitabilityService::class)
                ->ranking($companyId, $branchId, [], 5),
            'top_products' => app(\App\Support\Production\ProductProfitabilityService::class)
                ->ranking($companyId, $branchId, [], 5),
            'top_salespersons' => app(\App\Support\Production\SalespersonProfitabilityService::class)
                ->ranking($companyId, $branchId, [], 5),
            'report_url' => route('admin.reports.job-profitability.index'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function kpiStrip(): array
    {
        $user = auth()->user();
        $scope = $this->intelligenceScope();
        $kpis = [];

        if ($user?->can('quotations.view')) {
            $kpis[] = $this->kpi(
                __('Open Quotations'),
                (string) Quotation::query()->forTenant()->whereIn('status', [
                    QuotationStatus::Draft,
                    QuotationStatus::PendingApproval,
                    QuotationStatus::Sent,
                    QuotationStatus::Viewed,
                    QuotationStatus::Accepted,
                ])->count(),
                'document-text',
                'admin.quotations.dashboard',
            );
        }

        if ($user?->can('quotations.view') || $user?->can('commercial.approvals.view')) {
            $kpis[] = $this->kpi(
                __('Pending Approvals'),
                (string) Quotation::query()->forTenant()
                    ->where('status', QuotationStatus::PendingApproval)
                    ->count(),
                'clock',
                'admin.commercial.approvals.index',
            );
        }

        if ($user?->can('quotations.view')) {
            $kpis[] = $this->kpi(
                __('Accepted Quotations'),
                (string) Quotation::query()->forTenant()
                    ->where('status', QuotationStatus::Accepted)
                    ->count(),
                'check-circle',
                'admin.quotations.index',
            );
        }

        if ($user?->can('sales_orders.view')) {
            $kpis[] = $this->kpi(
                __('Converted Orders'),
                (string) SalesOrder::query()->forTenant()
                    ->whereHas('quotation', fn ($q) => $q->where('status', QuotationStatus::Converted))
                    ->count(),
                'clipboard-list',
                'admin.sales-orders.dashboard',
            );

            $kpis[] = $this->kpi(
                __('Production Ready Orders'),
                (string) SalesOrder::query()->forTenant()
                    ->where('status', SalesOrderStatus::ReadyForProduction)
                    ->count(),
                'clipboard-check',
                'admin.sales-orders.index',
            );
        }

        if ($user?->can('artwork.view')) {
            $kpis[] = $this->kpi(
                __('Awaiting Artwork Approval'),
                (string) ArtworkRequest::query()->forTenant()
                    ->whereIn('status', [
                        ArtworkRequestStatus::Submitted,
                        ArtworkRequestStatus::RevisionRequested,
                    ])
                    ->count(),
                'color-swatch',
                'admin.artwork.dashboard',
            );
        }

        if ($user?->can('invoices.view')) {
            $receivables = $this->intelligence->sumReceivables($scope);
            $kpis[] = $this->kpi(
                __('Outstanding Receivables'),
                $receivables !== null ? $this->money($receivables) : '—',
                'currency-dollar',
                'admin.invoices.index',
            );
        }

        if ($user?->can('sales_orders.view') || $user?->can('invoices.view')) {
            $revenue = $this->intelligence->sumRevenueMtd($scope);
            $kpis[] = $this->kpi(
                __('Revenue This Month'),
                $revenue !== null ? $this->money($revenue) : '—',
                'chart-bar',
                'commercial.reports.sales.index',
            );
        }

        return $kpis;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pipeline(): array
    {
        $user = auth()->user();
        $stages = [];

        if ($user?->can('public_leads.quote_requests.view')) {
            $stages[] = $this->pipelineStage(
                'quote_requests',
                __('Quote Requests'),
                (int) PublicQuoteRequest::query()
                    ->whereIn('status', [PublicQuoteRequestStatus::Pending, PublicQuoteRequestStatus::Reviewing])
                    ->count(),
                'admin.public-quote-requests.index',
            );
        }

        if ($user?->can('quotations.view')) {
            $stages[] = $this->pipelineStage(
                'quotations',
                __('Quotations'),
                (int) Quotation::query()->forTenant()->whereIn('status', [
                    QuotationStatus::Draft,
                    QuotationStatus::PendingApproval,
                    QuotationStatus::Sent,
                    QuotationStatus::Viewed,
                    QuotationStatus::Accepted,
                ])->count(),
                'admin.quotations.dashboard',
            );
        }

        if ($user?->can('artwork.view')) {
            $stages[] = $this->pipelineStage(
                'artwork',
                __('Artwork'),
                (int) ArtworkRequest::query()->forTenant()->whereIn('status', [
                    ArtworkRequestStatus::Requested,
                    ArtworkRequestStatus::InDesign,
                    ArtworkRequestStatus::Submitted,
                    ArtworkRequestStatus::RevisionRequested,
                ])->count(),
                'admin.artwork.dashboard',
            );
        }

        if ($user?->can('sales_orders.view')) {
            $stages[] = $this->pipelineStage(
                'sales_orders',
                __('Sales Orders'),
                (int) SalesOrder::query()->forTenant()->whereIn('status', [
                    SalesOrderStatus::Draft,
                    SalesOrderStatus::Confirmed,
                    SalesOrderStatus::ReadyForProduction,
                ])->count(),
                'admin.sales-orders.dashboard',
            );
        }

        if ($user?->can('production.view')) {
            $stages[] = $this->pipelineStage(
                'production',
                __('Production'),
                (int) ProductionJobCard::query()->forTenant()->whereIn('status', [
                    ProductionJobCardStatus::Queued,
                    ProductionJobCardStatus::InProduction,
                    ProductionJobCardStatus::QualityCheck,
                    ProductionJobCardStatus::Rework,
                    ProductionJobCardStatus::OnHold,
                ])->count(),
                'admin.production.job-cards.index',
            );
        }

        if ($user?->can('invoices.view')) {
            $stages[] = $this->pipelineStage(
                'invoices',
                __('Invoices'),
                (int) CustomerInvoice::query()->forTenant()
                    ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
                    ->count(),
                'admin.invoices.index',
            );
        }

        if ($user?->can('payments.view')) {
            $stages[] = $this->pipelineStage(
                'payments',
                __('Payments'),
                (int) CustomerPayment::query()->forTenant()
                    ->where('status', CustomerPaymentStatus::Posted)
                    ->count(),
                'admin.payments.index',
            );
        }

        if ($user?->can('dispatch.view')) {
            $stages[] = $this->pipelineStage(
                'dispatch',
                __('Dispatch'),
                (int) DeliveryNote::query()->forTenant()
                    ->whereIn('status', [DeliveryNoteStatus::Draft, DeliveryNoteStatus::Dispatched])
                    ->count(),
                'admin.dispatch.delivery-notes.index',
            );
        }

        $max = max(1, ...array_column($stages, 'count'));

        return array_map(function (array $stage) use ($max) {
            $stage['percent'] = (int) round(($stage['count'] / $max) * 100);

            return $stage;
        }, $stages);
    }

    /**
     * @return list<array{title: string, items: list<array<string, mixed>>}>
     */
    public function attentionCenter(): array
    {
        $user = auth()->user();
        $sections = [];

        if ($user?->can('quotations.view')) {
            $sections[] = [
                'title' => __('Quotes Awaiting Approval'),
                'items' => $this->mapQuotationAttention(
                    Quotation::query()->forTenant()
                        ->where('status', QuotationStatus::PendingApproval)
                        ->with('customer:id,company_name')
                        ->orderBy('updated_at')
                        ->limit(5)
                        ->get(),
                ),
                'route' => 'admin.commercial.approvals.index',
            ];
        }

        if ($user?->can('artwork.view')) {
            $sections[] = [
                'title' => __('Artwork Awaiting Approval'),
                'items' => $this->mapArtworkAttention(
                    ArtworkRequest::query()->forTenant()
                        ->whereIn('status', [
                            ArtworkRequestStatus::Submitted,
                            ArtworkRequestStatus::RevisionRequested,
                        ])
                        ->with('customer:id,company_name')
                        ->orderBy('updated_at')
                        ->limit(5)
                        ->get(),
                ),
                'route' => 'admin.artwork.index',
            ];
        }

        if ($user?->can('sales_orders.view')) {
            $sections[] = [
                'title' => __('Orders Awaiting Production'),
                'items' => $this->mapSalesOrderAttention(
                    SalesOrder::query()->forTenant()
                        ->where('status', SalesOrderStatus::Confirmed)
                        ->with('customer:id,company_name')
                        ->orderBy('updated_at')
                        ->limit(5)
                        ->get(),
                ),
                'route' => 'admin.sales-orders.index',
            ];

            $sections[] = [
                'title' => __('Orders Awaiting Invoice'),
                'items' => $this->mapSalesOrderAttention(
                    SalesOrder::query()->forTenant()
                        ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
                        ->whereColumn('invoiced_total', '<', 'total_amount')
                        ->with('customer:id,company_name')
                        ->orderBy('updated_at')
                        ->limit(5)
                        ->get(),
                ),
                'route' => 'admin.sales-orders.index',
            ];
        }

        if ($user?->can('invoices.view')) {
            $sections[] = [
                'title' => __('Outstanding Invoices'),
                'items' => $this->mapInvoiceAttention(
                    CustomerInvoice::query()->forTenant()
                        ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
                        ->where('balance_due', '>', 0)
                        ->with('customer:id,company_name')
                        ->orderByDesc('balance_due')
                        ->limit(5)
                        ->get(),
                ),
                'route' => 'admin.invoices.index',
            ];
        }

        if ($user?->can('sales_orders.view') && $user?->can('invoices.view')) {
            $sections[] = [
                'title' => __('Unpaid Orders'),
                'items' => $this->mapSalesOrderAttention(
                    SalesOrder::query()->forTenant()
                        ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
                        ->whereHas('invoices', fn ($q) => $q
                            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
                            ->where('balance_due', '>', 0))
                        ->with('customer:id,company_name')
                        ->orderBy('updated_at')
                        ->limit(5)
                        ->get(),
                ),
                'route' => 'admin.sales-orders.index',
            ];
        }

        return $sections;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function topCustomers(): array
    {
        if (! auth()->user()?->can('sales_orders.view') && ! auth()->user()?->can('crm.customers.view')) {
            return [];
        }

        $reportScope = $this->salesReportScope();
        $rows = $this->salesReportQueries->topCustomers($reportScope);
        $outstanding = $this->customerOutstandingBalances();

        return $rows->map(function (array $row, int $index) use ($outstanding, $reportScope) {
            $customerName = $row['customer'] ?? '—';
            $customerId = $this->resolveCustomerIdByName($customerName, $reportScope);

            return [
                'rank' => $index + 1,
                'customer' => $customerName,
                'customer_url' => $customerId && Route::has('admin.crm.customers.show')
                    ? route('admin.crm.customers.show', $customerId)
                    : null,
                'revenue' => $row['revenue'] ?? '—',
                'orders' => $row['orders'] ?? '—',
                'outstanding' => $customerId && isset($outstanding[$customerId])
                    ? $this->money((float) $outstanding[$customerId])
                    : '—',
                'margin' => null,
            ];
        })->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function quickActions(): array
    {
        $user = auth()->user();
        $actions = [
            ['label' => __('New Quotation'), 'route' => 'admin.quotations.create', 'permission' => 'quotations.create', 'variant' => 'primary'],
            ['label' => __('New Sales Order'), 'route' => 'admin.sales-orders.create', 'permission' => 'sales_orders.create', 'variant' => 'secondary'],
            ['label' => __('Artwork Queue'), 'route' => 'admin.artwork.dashboard', 'permission' => 'artwork.view', 'variant' => 'secondary'],
            ['label' => __('Approvals Queue'), 'route' => 'admin.commercial.approvals.index', 'permission' => 'commercial.approvals.view', 'variant' => 'secondary'],
            ['label' => __('Commercial Reports'), 'route' => 'commercial.reports.sales.index', 'permission' => 'commercial.reports.sales.view', 'variant' => 'secondary'],
        ];

        $presented = [];

        foreach ($actions as $action) {
            if (! $user?->can($action['permission'])) {
                continue;
            }

            if (! Route::has($action['route'])) {
                continue;
            }

            $presented[] = [
                'label' => $action['label'],
                'href' => route($action['route']),
                'variant' => $action['variant'],
            ];
        }

        return $presented;
    }

    /**
     * @return array<string, float>
     */
    protected function customerOutstandingBalances(): array
    {
        if (! auth()->user()?->can('invoices.view')) {
            return [];
        }

        return CustomerInvoice::query()->forTenant()
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->where('balance_due', '>', 0)
            ->select('customer_id', DB::raw('SUM(balance_due) as outstanding'))
            ->groupBy('customer_id')
            ->pluck('outstanding', 'customer_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    protected function resolveCustomerIdByName(string $name, CommercialSalesReportScope $scope): ?int
    {
        if ($name === '—') {
            return null;
        }

        return Customer::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('company_name', $name)
            ->value('id');
    }

    protected function intelligenceScope(): IntelligenceScope
    {
        return new IntelligenceScope(
            companyId: (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            branchId: tenant()->branchId(),
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );
    }

    protected function salesReportScope(): CommercialSalesReportScope
    {
        return new CommercialSalesReportScope(
            companyId: (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            branchId: tenant()->branchId(),
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
            topLimit: 10,
            topBy: 'revenue',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function kpi(string $label, string $value, string $icon, ?string $route): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'href' => $route && Route::has($route) ? route($route) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pipelineStage(string $key, string $label, int $count, ?string $route): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'count' => $count,
            'route' => $route && Route::has($route) ? $route : null,
        ];
    }

    /**
     * @param  Collection<int, Quotation>  $quotations
     * @return list<array<string, mixed>>
     */
    protected function mapQuotationAttention(Collection $quotations): array
    {
        return $quotations->map(fn (Quotation $quotation) => [
            'reference' => $quotation->quotation_number,
            'customer' => $quotation->customer?->company_name ?? __('Walk-in'),
            'amount' => number_format((float) $quotation->total_amount, 2),
            'date' => $quotation->updated_at,
            'url' => Route::has('admin.quotations.show') ? route('admin.quotations.show', $quotation) : null,
        ])->all();
    }

    /**
     * @param  Collection<int, ArtworkRequest>  $requests
     * @return list<array<string, mixed>>
     */
    protected function mapArtworkAttention(Collection $requests): array
    {
        return $requests->map(fn (ArtworkRequest $request) => [
            'reference' => $request->request_number,
            'customer' => $request->customer?->company_name ?? '—',
            'amount' => '—',
            'date' => $request->updated_at,
            'url' => Route::has('admin.artwork.show') ? route('admin.artwork.show', $request) : null,
        ])->all();
    }

    /**
     * @param  Collection<int, SalesOrder>  $orders
     * @return list<array<string, mixed>>
     */
    protected function mapSalesOrderAttention(Collection $orders): array
    {
        return $orders->map(fn (SalesOrder $order) => [
            'reference' => $order->order_number,
            'customer' => $order->customer?->company_name ?? '—',
            'amount' => number_format((float) $order->total_amount, 2),
            'date' => $order->updated_at,
            'url' => Route::has('admin.sales-orders.show') ? route('admin.sales-orders.show', $order) : null,
        ])->all();
    }

    /**
     * @param  Collection<int, CustomerInvoice>  $invoices
     * @return list<array<string, mixed>>
     */
    protected function mapInvoiceAttention(Collection $invoices): array
    {
        return $invoices->map(fn (CustomerInvoice $invoice) => [
            'reference' => $invoice->invoice_number,
            'customer' => $invoice->customer?->company_name ?? '—',
            'amount' => number_format((float) $invoice->balance_due, 2),
            'date' => $invoice->invoice_date,
            'url' => Route::has('admin.invoices.show') ? route('admin.invoices.show', $invoice) : null,
        ])->all();
    }

    protected function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }
}
