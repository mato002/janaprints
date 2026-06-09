<?php

namespace App\Support\Reports;

use App\Enums\CustomerInvoiceStatus;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Financial360Presenter
{
    use BuildsIntelligenceSections;

    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
        protected IntelligenceAggregateQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request, includeCustomer: true);
        $scope = $resolved['scope'];

        return [
            'title' => __('Financial 360'),
            'description' => __('Revenue, receivables, payables, and aging intelligence.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'export_route' => 'admin.reports.intelligence360.export',
            'export_route_params' => ['reportKey' => 'financial'],
            'sections' => [
                $this->summary($scope),
                $this->revenueIntelligence($scope),
                $this->receivablesSection($scope),
                $this->payablesSection($scope),
                $this->cashPlaceholder(),
                $this->branchFinancial($scope),
                $this->customerExposure($scope),
                $this->attention($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(IntelligenceScope $scope): array
    {
        $revenue = $this->queries->sumRevenueMtd($scope);
        $recv = $this->queries->sumReceivables($scope);
        $pay = $this->queries->sumPayables($scope);

        return $this->kpiSection(__('Financial Summary'), [
            $this->kpi(__('Revenue (period)'), $revenue !== null ? $this->queries->money($revenue) : '—', 'chart-bar', pending: $revenue === null),
            $this->kpi(__('Receivables'), $recv !== null ? $this->queries->money($recv) : '—', 'cash', pending: $recv === null),
            $this->kpi(__('Payables'), $pay !== null ? $this->queries->money($pay) : '—', 'currency-dollar', pending: $pay === null),
            $this->kpi(__('Cash position'), '—', 'banknotes', __('Module not ready')),
            $this->kpi(__('Expenses (period)'), '—', 'chart-pie', __('Pending source')),
            $this->kpi(__('Profit'), '—', 'scale', __('Pending costing')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function revenueIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('customer_invoices') && ! $this->queries->hasTable('sales_orders')) {
            return $this->pendingSection(__('Revenue Intelligence'));
        }

        $byBranch = $this->queries->hasTable('sales_orders')
            ? SalesOrder::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereDate('order_date', '>=', $scope->fromDate)
                ->whereDate('order_date', '<=', $scope->toDate)
                ->select('branch_id', DB::raw('SUM(total_amount) as revenue'))
                ->groupBy('branch_id')
                ->get()
            : collect();

        $branchNames = Branch::query()->whereIn('id', $byBranch->pluck('branch_id'))->pluck('name', 'id');

        $posted = $this->queries->hasTable('customer_invoices')
            ? (int) $this->queries->scoped(CustomerInvoice::class, $scope)->where('status', CustomerInvoiceStatus::Posted)->count()
            : 0;
        $draft = $this->queries->hasTable('customer_invoices')
            ? (int) $this->queries->scoped(CustomerInvoice::class, $scope)->where('status', CustomerInvoiceStatus::Draft)->count()
            : 0;

        return [
            'type' => 'split',
            'title' => __('Revenue Intelligence'),
            'kpis' => [
                $this->kpi(__('Posted invoices'), (string) $posted, 'document-text'),
                $this->kpi(__('Draft invoices'), (string) $draft, 'inbox'),
            ],
            'tables' => [
                $this->tableSection(
                    __('Revenue by Branch'),
                    [__('Branch'), __('Revenue')],
                    $byBranch->map(fn ($r) => ['branch' => $branchNames[$r->branch_id] ?? '—', 'revenue' => $this->queries->money((float) $r->revenue)])->all(),
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function receivablesSection(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('customer_invoices')) {
            return $this->pendingSection(__('Receivables Intelligence'));
        }

        $total = $this->queries->sumReceivables($scope) ?? 0.0;
        $buckets = $this->queries->receivableAgingBuckets($scope);

        $top = $this->queries->scoped(CustomerInvoice::class, $scope)
            ->whereIn('status', [CustomerInvoiceStatus::Posted, CustomerInvoiceStatus::Approved])
            ->where('balance_due', '>', 0)
            ->select('customer_id', DB::raw('SUM(balance_due) as outstanding'))
            ->groupBy('customer_id')
            ->orderByDesc('outstanding')
            ->limit(10)
            ->get();

        $names = Customer::query()->whereIn('id', $top->pluck('customer_id'))->pluck('company_name', 'id');

        return [
            'type' => 'split',
            'title' => __('Receivables Intelligence'),
            'kpis' => array_merge(
                [$this->kpi(__('Total receivables'), $this->queries->money($total), 'cash')],
                collect($buckets)->map(fn ($b) => $this->kpi($b['label'], $b['amount_display'], 'clock'))->all(),
            ),
            'tables' => [
                $this->tableSection(
                    __('Top Outstanding Customers'),
                    [__('Customer'), __('Outstanding')],
                    $top->map(fn ($r) => ['customer' => $names[$r->customer_id] ?? '—', 'outstanding' => $this->queries->money((float) $r->outstanding)])->all(),
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function payablesSection(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('supplier_bills')) {
            return $this->pendingSection(__('Payables Intelligence'));
        }

        $total = $this->queries->sumPayables($scope) ?? 0.0;
        $buckets = $this->queries->payableAgingBuckets($scope);

        return [
            'type' => 'split',
            'title' => __('Payables Intelligence'),
            'kpis' => array_merge(
                [$this->kpi(__('Total payables'), $this->queries->money($total), 'currency-dollar')],
                collect($buckets)->map(fn ($b) => $this->kpi($b['label'], $b['amount_display'], 'clock'))->all(),
            ),
            'tables' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function cashPlaceholder(): array
    {
        return $this->kpiSection(__('Cash / Bank'), [
            $this->kpi(__('Cash balance'), '—', 'banknotes', __('Module not ready'), true),
            $this->kpi(__('Bank balance'), '—', 'currency-dollar', __('Module not ready'), true),
            $this->kpi(__('Collections (period)'), '—', 'cash', __('Module not ready'), true),
            $this->kpi(__('Payments (period)'), '—', 'currency-dollar', __('Module not ready'), true),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function branchFinancial(IntelligenceScope $scope): array
    {
        $rows = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->when($scope->branchId, fn ($q) => $q->where('id', $scope->branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Branch $branch) use ($scope) {
                $scoped = new IntelligenceScope($scope->companyId, $branch->id, $scope->fromDate, $scope->toDate);

                return [
                    'branch' => $branch->name,
                    'revenue' => $this->queries->money($this->queries->sumSalesOrderValue($scoped, true)),
                    'receivables' => ($r = $this->queries->sumReceivables($scoped)) !== null ? $this->queries->money($r) : '—',
                    'payables' => ($p = $this->queries->sumPayables($scoped)) !== null ? $this->queries->money($p) : '—',
                    'cash' => '—',
                    'profit' => '—',
                ];
            })
            ->all();

        return $this->tableSection(
            __('Branch Financial Performance'),
            [__('Branch'), __('Revenue'), __('Receivables'), __('Payables'), __('Cash'), __('Profit')],
            $rows,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function customerExposure(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('customer_invoices')) {
            return $this->pendingSection(__('Customer Financial Exposure'));
        }

        $rows = $this->queries->scoped(CustomerInvoice::class, $scope)
            ->whereIn('status', [CustomerInvoiceStatus::Posted, CustomerInvoiceStatus::Approved])
            ->select(
                'customer_id',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('SUM(balance_due) as outstanding'),
                DB::raw('MAX(invoice_date) as last_invoice'),
            )
            ->groupBy('customer_id')
            ->orderByDesc('outstanding')
            ->limit(20)
            ->get();

        $names = Customer::query()->whereIn('id', $rows->pluck('customer_id'))->pluck('company_name', 'id');

        return $this->tableSection(
            __('Customer Financial Exposure'),
            [__('Customer'), __('Revenue'), __('Outstanding'), __('Last Invoice')],
            $rows->map(fn ($r) => [
                'customer' => $names[$r->customer_id] ?? '—',
                'revenue' => $this->queries->money((float) $r->revenue),
                'outstanding' => $this->queries->money((float) $r->outstanding),
                'last' => $r->last_invoice,
            ])->all(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function attention(IntelligenceScope $scope): array
    {
        return [
            'type' => 'attention',
            'title' => __('Financial Attention Center'),
            'items' => [
                ['label' => __('Outstanding receivables'), 'count' => null, 'display' => ($r = $this->queries->sumReceivables($scope)) !== null ? $this->queries->money($r) : '—', 'severity' => 'warning'],
                ['label' => __('Outstanding payables'), 'count' => null, 'display' => ($p = $this->queries->sumPayables($scope)) !== null ? $this->queries->money($p) : '—', 'severity' => 'warning'],
                ['label' => __('Cash/bank source'), 'count' => null, 'display' => '—', 'severity' => 'muted', 'hint' => __('Module not ready')],
                ['label' => __('Profitability'), 'count' => null, 'display' => '—', 'severity' => 'muted', 'hint' => __('Pending costing')],
            ],
        ];
    }
}
