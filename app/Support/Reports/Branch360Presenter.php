<?php

namespace App\Support\Reports;

use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Sales\SalesOrder;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Branch360Presenter
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
        $resolved = $this->scopeResolver->resolve($request, defaultBranchFromTenant: false);
        $scope = $resolved['scope'];

        return [
            'title' => __('Branch 360'),
            'description' => __('Branch performance comparison and operational snapshot.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'detail_mode' => $scope->branchId !== null,
            'sections' => $scope->branchId
                ? [$this->branchDetail($scope)]
                : [$this->comparisonTable($scope), $this->attention($scope)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function branchDetail(IntelligenceScope $scope): array
    {
        $branch = Branch::query()->find($scope->branchId);

        return $this->kpiSection($branch?->name ?? __('Branch'), [
            $this->kpi(__('Customers'), (string) $this->queries->countCustomers($scope), 'user-circle'),
            $this->kpi(__('Leads'), (string) $this->queries->countLeads($scope), 'sparkles'),
            $this->kpi(__('Quotations (period)'), (string) $this->queries->countQuotationsInPeriod($scope), 'document-text'),
            $this->kpi(__('Sales Orders (period)'), (string) $this->queries->countSalesOrders($scope, null, true), 'clipboard-list'),
            $this->kpi(__('Active Jobs'), (string) $this->queries->countActiveJobs($scope), 'cog'),
            $this->kpi(__('Completed Jobs'), (string) $this->queries->countCompletedJobsInPeriod($scope), 'check-circle'),
            $this->kpi(__('Inventory Value'), ($v = $this->queries->inventoryValue($scope)) !== null ? $this->queries->money($v) : '—', 'cube', pending: $v === null),
            $this->kpi(__('Receivables'), ($r = $this->queries->sumReceivables($scope)) !== null ? $this->queries->money($r) : '—', 'cash', pending: $r === null),
            $this->kpi(__('Payables'), ($p = $this->queries->sumPayables($scope)) !== null ? $this->queries->money($p) : '—', 'currency-dollar', pending: $p === null),
            $this->kpi(__('Revenue (period)'), $this->queries->money($this->queries->sumSalesOrderValue($scope, true)), 'chart-bar'),
            $this->kpi(__('Profit'), '—', 'chart-pie', __('Pending costing')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function comparisonTable(IntelligenceScope $scope): array
    {
        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $rows = $branches->map(function (Branch $branch) use ($scope) {
            $scoped = new IntelligenceScope($scope->companyId, $branch->id, $scope->fromDate, $scope->toDate);

            return [
                'branch' => $branch->name,
                'customers' => (string) $this->queries->countCustomers($scoped),
                'sales' => $this->queries->money($this->queries->sumSalesOrderValue($scoped, true)),
                'jobs' => (string) $this->queries->countActiveJobs($scoped),
                'inventory' => ($v = $this->queries->inventoryValue($scoped)) !== null ? $this->queries->money($v) : '—',
                'receivables' => ($r = $this->queries->sumReceivables($scoped)) !== null ? $this->queries->money($r) : '—',
                'pos' => (string) $this->queries->countPendingPurchaseOrders($scoped),
                'alerts' => (string) ($this->queries->countDelayedJobs($scoped) + $this->queries->countLowStockAlerts($scoped)),
            ];
        })->all();

        return $this->tableSection(
            __('Branch Comparison'),
            [__('Branch'), __('Customers'), __('Sales'), __('Jobs'), __('Inventory'), __('Receivables'), __('POs'), __('Alerts')],
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
                ['label' => __('Delayed jobs (all branches)'), 'count' => $this->queries->countDelayedJobs($scope), 'severity' => 'danger'],
                ['label' => __('Low stock alerts'), 'count' => $this->queries->countLowStockAlerts($scope), 'severity' => 'warning'],
                ['label' => __('Pending PRs'), 'count' => $this->queries->countPendingPurchaseRequests($scope), 'severity' => 'warning'],
                ['label' => __('Outstanding receivables'), 'count' => null, 'display' => ($r = $this->queries->sumReceivables($scope)) !== null ? $this->queries->money($r) : '—', 'severity' => 'muted'],
            ],
        ];
    }
}
