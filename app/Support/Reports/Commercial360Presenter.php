<?php

namespace App\Support\Reports;

use App\Enums\CustomerType;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Commercial360Presenter
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
            'title' => __('Commercial 360'),
            'description' => __('Sales and customer management intelligence (not Customer 360).'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'export_route' => 'admin.reports.intelligence360.export',
            'export_route_params' => ['reportKey' => 'commercial'],
            'sections' => [
                $this->summary($scope),
                $this->leadIntelligence($scope),
                $this->quotationIntelligence($scope),
                $this->salesOrderIntelligence($scope),
                $this->segmentIntelligence($scope),
                $this->topCustomers($scope),
                $this->pipeline($scope),
                $this->branchCommercial($scope),
                $this->attention($scope),
                $this->trends($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(IntelligenceScope $scope): array
    {
        return $this->kpiSection(__('Commercial Summary'), [
            $this->kpi(__('Total customers'), (string) $this->queries->countCustomers($scope), 'user-circle'),
            $this->kpi(__('New customers (period)'), (string) $this->queries->countCustomersCreatedInPeriod($scope), 'sparkles'),
            $this->kpi(__('Leads'), (string) $this->queries->countLeads($scope), 'collection'),
            $this->kpi(__('Sales orders (period)'), (string) $this->queries->countSalesOrders($scope, null, true), 'clipboard-list'),
            $this->kpi(__('Commercial value'), $this->queries->money($this->queries->sumSalesOrderValue($scope, true)), 'currency-dollar'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function leadIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('leads')) {
            return $this->pendingSection(__('Lead Intelligence'));
        }

        $total = $this->queries->countLeads($scope);
        $won = $this->queries->countLeads($scope, LeadStatus::Won);
        $lost = $this->queries->countLeads($scope, LeadStatus::Lost);
        $open = $this->queries->countLeads($scope, LeadStatus::Open);

        $byBranch = $this->queries->scoped(Lead::class, $scope)
            ->select('branch_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('branch_id')
            ->get();
        $branchNames = Branch::query()->whereIn('id', $byBranch->pluck('branch_id'))->pluck('name', 'id');

        return [
            'type' => 'split',
            'title' => __('Lead Intelligence'),
            'kpis' => [],
            'tables' => [
                $this->tableSection(
                    __('Lead Status'),
                    [__('Status'), __('Count')],
                    [
                        ['status' => __('Open'), 'count' => (string) $open],
                        ['status' => __('Converted'), 'count' => (string) $won],
                        ['status' => __('Lost'), 'count' => (string) $lost],
                        ['status' => __('Total'), 'count' => (string) $total],
                    ],
                ),
                $this->tableSection(
                    __('Leads by Branch'),
                    [__('Branch'), __('Leads')],
                    $byBranch->map(fn ($r) => ['branch' => $branchNames[$r->branch_id] ?? '—', 'count' => (string) $r->cnt])->all(),
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function quotationIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('quotations')) {
            return $this->pendingSection(__('Quotation Intelligence'));
        }

        $created = $this->queries->countQuotationsInPeriod($scope);
        $sent = $this->queries->countQuotationsInPeriod($scope, [QuotationStatus::Sent, QuotationStatus::Viewed]);
        $approved = $this->queries->countQuotationsInPeriod($scope, [QuotationStatus::Accepted]);
        $rejected = $this->queries->countQuotationsInPeriod($scope, [QuotationStatus::Rejected]);
        $expired = $this->queries->countQuotationsInPeriod($scope, [QuotationStatus::Expired]);
        $value = $this->queries->sumQuotationValueInPeriod($scope);
        $avg = $created > 0 ? $value / $created : 0;
        $rate = $created > 0 ? round(($approved / $created) * 100).'%' : '0%';

        return $this->tableSection(
            __('Quotation Intelligence'),
            [__('Metric'), __('Value')],
            [
                ['metric' => __('Created'), 'value' => (string) $created],
                ['metric' => __('Sent'), 'value' => (string) $sent],
                ['metric' => __('Approved'), 'value' => (string) $approved],
                ['metric' => __('Rejected'), 'value' => (string) $rejected],
                ['metric' => __('Expired'), 'value' => (string) $expired],
                ['metric' => __('Quotation value'), 'value' => $this->queries->money($value)],
                ['metric' => __('Average value'), 'value' => $this->queries->money($avg)],
                ['metric' => __('Acceptance rate'), 'value' => $rate],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function salesOrderIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('sales_orders')) {
            return $this->pendingSection(__('Sales Order Intelligence'));
        }

        $base = $this->queries->scoped(SalesOrder::class, $scope)
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate);

        $created = (clone $base)->count();
        $confirmed = (clone $base)->where('status', SalesOrderStatus::Confirmed)->count();
        $completed = (clone $base)->where('status', SalesOrderStatus::Completed)->count();
        $cancelled = (clone $base)->where('status', SalesOrderStatus::Cancelled)->count();
        $orderValue = $this->queries->sumSalesOrderValue($scope, true);

        return $this->tableSection(
            __('Sales Order Intelligence'),
            [__('Status'), __('Orders'), __('Value')],
            [
                ['status' => __('Created'), 'orders' => (string) $created, 'value' => $this->queries->money($orderValue)],
                ['status' => __('Confirmed'), 'orders' => (string) $confirmed, 'value' => '—'],
                ['status' => __('Completed'), 'orders' => (string) $completed, 'value' => '—'],
                ['status' => __('Cancelled'), 'orders' => (string) $cancelled, 'value' => '—'],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function segmentIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('customers')) {
            return $this->pendingSection(__('Customer Segment Intelligence'));
        }

        $rows = $this->queries->scoped(Customer::class, $scope)
            ->select('customer_type', DB::raw('COUNT(*) as cnt'))
            ->groupBy('customer_type')
            ->get()
            ->map(fn ($r) => [
                'segment' => $r->customer_type instanceof CustomerType ? $r->customer_type->value : (string) $r->customer_type,
                'count' => (string) $r->cnt,
            ])
            ->all();

        if ($rows === []) {
            return $this->pendingSection(__('Customer Segment Intelligence'));
        }

        return $this->tableSection(__('Customer Segments'), [__('Segment'), __('Customers')], $rows);
    }

    /**
     * @return array<string, mixed>
     */
    protected function topCustomers(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('sales_orders')) {
            return $this->pendingSection(__('Top Customers'));
        }

        $rows = $this->queries->scoped(SalesOrder::class, $scope)
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit(20)
            ->get();

        $names = Customer::query()->whereIn('id', $rows->pluck('customer_id'))->pluck('company_name', 'id');

        return $this->tableSection(
            __('Top Customers'),
            [__('Customer'), __('Revenue'), __('Orders'), __('Outstanding'), __('Last Activity')],
            $rows->map(fn ($r) => [
                'customer' => $names[$r->customer_id] ?? '—',
                'revenue' => $this->queries->money((float) $r->revenue),
                'orders' => (string) $r->orders,
                'outstanding' => '—',
                'activity' => '—',
            ])->all(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function pipeline(IntelligenceScope $scope): array
    {
        $stages = [
            ['label' => __('Lead'), 'count' => $this->queries->countLeads($scope, LeadStatus::Open)],
            ['label' => __('Qualified'), 'count' => $this->queries->countLeads($scope, LeadStatus::Won)],
            ['label' => __('Quotation'), 'count' => $this->queries->countQuotations($scope, [QuotationStatus::Sent, QuotationStatus::Viewed, QuotationStatus::PendingApproval])],
            ['label' => __('Approved'), 'count' => $this->queries->countQuotations($scope, [QuotationStatus::Accepted])],
            ['label' => __('Sales Order'), 'count' => $this->queries->countSalesOrders($scope, [SalesOrderStatus::Confirmed, SalesOrderStatus::ReadyForProduction, SalesOrderStatus::InProduction])],
            ['label' => __('Production'), 'count' => $this->queries->countSalesOrders($scope, [SalesOrderStatus::InProduction])],
        ];

        return ['type' => 'pipeline', 'title' => __('Commercial Pipeline'), 'stages' => $stages];
    }

    /**
     * @return array<string, mixed>
     */
    protected function branchCommercial(IntelligenceScope $scope): array
    {
        $rows = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->when($scope->branchId, fn ($q) => $q->where('id', $scope->branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Branch $branch) use ($scope) {
                $scoped = new IntelligenceScope($scope->companyId, $branch->id, $scope->fromDate, $scope->toDate);
                $leads = $this->queries->countLeads($scoped);
                $won = $this->queries->countLeads($scoped, LeadStatus::Won);

                return [
                    'branch' => $branch->name,
                    'leads' => (string) $leads,
                    'customers' => (string) $this->queries->countCustomers($scoped),
                    'quotations' => (string) $this->queries->countQuotationsInPeriod($scoped),
                    'orders' => (string) $this->queries->countSalesOrders($scoped, null, true),
                    'revenue' => $this->queries->money($this->queries->sumSalesOrderValue($scoped, true)),
                    'conversion' => $leads > 0 ? round(($won / $leads) * 100).'%' : '0%',
                ];
            })
            ->all();

        return $this->tableSection(
            __('Branch Commercial Performance'),
            [__('Branch'), __('Leads'), __('Customers'), __('Quotations'), __('Orders'), __('Revenue'), __('Conversion')],
            $rows,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function attention(IntelligenceScope $scope): array
    {
        return [
            'type' => 'attention',
            'title' => __('Attention Center'),
            'items' => [
                ['label' => __('Open leads'), 'count' => $this->queries->countLeads($scope, LeadStatus::Open), 'severity' => 'warning'],
                ['label' => __('Expired quotations'), 'count' => $this->queries->countQuotations($scope, [QuotationStatus::Expired]), 'severity' => 'danger'],
                ['label' => __('Orders awaiting production'), 'count' => $this->queries->countSalesOrders($scope, [SalesOrderStatus::ReadyForProduction]), 'severity' => 'warning'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function trends(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('sales_orders')) {
            return $this->pendingSection(__('Trend Analysis'));
        }

        $start = now()->subDays(29)->toDateString();
        $leadTrend = $this->queries->hasTable('leads')
            ? (int) $this->queries->scoped(Lead::class, $scope)->whereDate('created_at', '>=', $start)->count()
            : 0;
        $quoteTrend = (int) $this->queries->scoped(Quotation::class, $scope)->whereDate('quotation_date', '>=', $start)->count();
        $orderTrend = (int) $this->queries->scoped(SalesOrder::class, $scope)->whereDate('order_date', '>=', $start)->count();

        return $this->tableSection(
            __('Last 30 Days'),
            [__('Metric'), __('Count / Value')],
            [
                ['metric' => __('Leads'), 'value' => (string) $leadTrend],
                ['metric' => __('Quotations'), 'value' => (string) $quoteTrend],
                ['metric' => __('Sales orders'), 'value' => (string) $orderTrend],
                ['metric' => __('Revenue'), 'value' => $this->queries->money($this->queries->sumSalesOrderValue($scope, true))],
            ],
        );
    }
}
