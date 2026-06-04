<?php

namespace App\Support\Reports;

use App\Enums\LeadStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;

class KpiCenterPresenter
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
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];
        $category = $scope->kpiCategory;

        $groups = [
            'commercial' => $this->commercialKpis($scope),
            'production' => $this->productionKpis($scope),
            'inventory' => $this->inventoryKpis($scope),
            'procurement' => $this->procurementKpis($scope),
            'accounting' => $this->accountingKpis($scope),
            'hr' => $this->hrKpis($scope),
        ];

        if ($category && isset($groups[$category])) {
            $groups = [$category => $groups[$category]];
        }

        return [
            'title' => __('KPI Center'),
            'description' => __('Read-only performance scorecards across ERP modules.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'kpi_groups' => $groups,
            'group_labels' => $this->groupLabels(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function groupLabels(): array
    {
        return [
            'commercial' => __('Commercial KPIs'),
            'production' => __('Production KPIs'),
            'inventory' => __('Inventory KPIs'),
            'procurement' => __('Procurement KPIs'),
            'accounting' => __('Accounting KPIs'),
            'hr' => __('HR KPIs'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function commercialKpis(IntelligenceScope $scope): array
    {
        $cards = [];

        if ($this->queries->hasTable('leads')) {
            $leads = $this->queries->countLeads($scope);
            $won = $this->queries->countLeads($scope, LeadStatus::Won);
            $cards[] = $this->kpiCard(
                __('Lead conversion rate'),
                $leads > 0 ? round(($won / $leads) * 100).'%' : '0%',
                __('Won / total leads'),
                $leads > 0 ? 'good' : 'watch',
                'CRM',
            );
        } else {
            $cards[] = $this->kpiCard(__('Lead conversion rate'), '—', __('Won / total leads'), 'pending', 'CRM');
        }

        if ($this->queries->hasTable('quotations')) {
            $quotes = $this->queries->countQuotationsInPeriod($scope);
            $accepted = $this->queries->countQuotationsInPeriod($scope, [QuotationStatus::Accepted, QuotationStatus::Converted]);
            $avgQuote = $quotes > 0 ? $this->queries->sumQuotationValueInPeriod($scope) / $quotes : 0;
            $cards[] = $this->kpiCard(
                __('Quotation acceptance rate'),
                $quotes > 0 ? round(($accepted / $quotes) * 100).'%' : '0%',
                __('Accepted / quotes in period'),
                $quotes > 0 ? 'good' : 'watch',
                'Sales',
            );
            $cards[] = $this->kpiCard(__('Average quotation value'), $this->queries->money($avgQuote), __('Total / count in period'), 'good', 'Sales');
        } else {
            $cards[] = $this->kpiCard(__('Quotation acceptance rate'), '—', __('Accepted / quotes in period'), 'pending', 'Sales');
            $cards[] = $this->kpiCard(__('Average quotation value'), '—', __('Total / count in period'), 'pending', 'Sales');
        }

        $cards[] = $this->queries->hasTable('customers')
            ? $this->kpiCard(__('Customer count'), (string) $this->queries->countCustomers($scope), __('Active directory'), 'good', 'CRM')
            : $this->kpiCard(__('Customer count'), '—', __('Active directory'), 'pending', 'CRM');

        $cards[] = $this->queries->hasTable('customers')
            ? $this->kpiCard(__('New customers this period'), (string) $this->queries->countCustomersCreatedInPeriod($scope), __('Created in range'), 'good', 'CRM')
            : $this->kpiCard(__('New customers this period'), '—', __('Created in range'), 'pending', 'CRM');

        $cards[] = $this->queries->hasTable('sales_orders')
            ? $this->kpiCard(__('Sales order value this period'), $this->queries->money($this->queries->sumSalesOrderValue($scope, true)), __('Confirmed orders'), 'good', 'Sales')
            : $this->kpiCard(__('Sales order value this period'), '—', __('Confirmed orders'), 'pending', 'Sales');

        return $cards;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function productionKpis(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return [
                $this->kpiCard(__('Jobs completed'), '—', __('Period'), 'pending', 'Production'),
                $this->kpiCard(__('Active jobs'), '—', __('In progress'), 'pending', 'Production'),
                $this->kpiCard(__('Delayed jobs'), '—', __('Past planned end'), 'pending', 'Production'),
                $this->kpiCard(__('Job completion rate'), '—', __('Completed / active+completed'), 'pending', 'Production'),
                $this->kpiCard(__('Average turnaround'), '—', __('Awaiting schedule fields'), 'pending', 'Production'),
            ];
        }

        $active = $this->queries->countActiveJobs($scope);
        $completed = $this->queries->countCompletedJobsInPeriod($scope);
        $delayed = $this->queries->countDelayedJobs($scope);
        $total = $active + $completed;
        $rate = $total > 0 ? round(($completed / $total) * 100).'%' : '0%';

        return [
            $this->kpiCard(__('Jobs completed'), (string) $completed, __('Period'), 'good', 'Production'),
            $this->kpiCard(__('Active jobs'), (string) $active, __('In progress'), $active > 20 ? 'watch' : 'good', 'Production'),
            $this->kpiCard(__('Delayed jobs'), (string) $delayed, __('Past planned end'), $delayed > 0 ? 'critical' : 'good', 'Production'),
            $this->kpiCard(__('Job completion rate'), $rate, __('Completed / active+completed'), 'good', 'Production'),
            $this->kpiCard(__('Average turnaround'), '—', __('Awaiting schedule fields'), 'pending', 'Production'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function inventoryKpis(IntelligenceScope $scope): array
    {
        $value = $this->queries->inventoryValue($scope);
        $lowStock = $this->queries->hasTable('inventory_reorder_alerts')
            ? $this->queries->countLowStockAlerts($scope)
            : null;

        return [
            $value !== null
                ? $this->kpiCard(__('Inventory value'), $this->queries->money($value), __('Valuation aggregate'), 'good', 'Inventory')
                : $this->kpiCard(__('Inventory value'), '—', __('Valuation aggregate'), 'pending', 'Inventory'),
            $lowStock !== null
                ? $this->kpiCard(__('Low stock count'), (string) $lowStock, __('Reorder alerts'), $lowStock > 0 ? 'watch' : 'good', 'Inventory')
                : $this->kpiCard(__('Low stock count'), '—', __('Reorder alerts'), 'pending', 'Inventory'),
            $this->kpiCard(__('Out of stock count'), '—', __('Insufficient movement data'), 'pending', 'Inventory'),
            $this->kpiCard(__('Negative stock count'), '—', __('Insufficient movement data'), 'pending', 'Inventory'),
            $this->kpiCard(__('Stock turnover'), '—', __('Insufficient period data'), 'pending', 'Inventory'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function procurementKpis(IntelligenceScope $scope): array
    {
        return [
            $this->queries->hasTable('purchase_requests')
                ? $this->kpiCard(__('Open purchase requests'), (string) $this->queries->countPendingPurchaseRequests($scope), __('Submitted/approved'), 'watch', 'Procurement')
                : $this->kpiCard(__('Open purchase requests'), '—', __('Submitted/approved'), 'pending', 'Procurement'),
            $this->queries->hasTable('purchase_orders')
                ? $this->kpiCard(__('Open purchase orders'), (string) $this->queries->countPendingPurchaseOrders($scope), __('Awaiting receipt'), 'watch', 'Procurement')
                : $this->kpiCard(__('Open purchase orders'), '—', __('Awaiting receipt'), 'pending', 'Procurement'),
            $this->queries->hasTable('purchase_orders')
                ? $this->kpiCard(__('Goods awaiting receipt'), (string) $this->queries->countGoodsAwaitingReceipt($scope), __('Sent/partial POs'), 'watch', 'Procurement')
                : $this->kpiCard(__('Goods awaiting receipt'), '—', __('Sent/partial POs'), 'pending', 'Procurement'),
            $this->queries->hasTable('vendors')
                ? $this->kpiCard(__('Vendor count'), (string) $this->queries->countVendors($scope), __('Active vendors'), 'good', 'Procurement')
                : $this->kpiCard(__('Vendor count'), '—', __('Active vendors'), 'pending', 'Procurement'),
            $this->queries->hasTable('purchase_orders')
                ? $this->kpiCard(__('Procurement value this period'), $this->queries->money($this->queries->sumProcurementValueInPeriod($scope)), __('PO total'), 'good', 'Procurement')
                : $this->kpiCard(__('Procurement value this period'), '—', __('PO total'), 'pending', 'Procurement'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function accountingKpis(IntelligenceScope $scope): array
    {
        $revenue = $this->queries->sumRevenueMtd($scope);
        $recv = $this->queries->sumReceivables($scope);
        $pay = $this->queries->sumPayables($scope);

        return [
            $this->kpiCard(__('Revenue this period'), $revenue !== null ? $this->queries->money($revenue) : '—', __('Posted invoices or orders'), $revenue !== null ? 'good' : 'pending', 'Accounting'),
            $this->kpiCard(__('Receivables'), $recv !== null ? $this->queries->money($recv) : '—', __('Outstanding balance'), $recv !== null ? 'watch' : 'pending', 'Accounting'),
            $this->kpiCard(__('Payables'), $pay !== null ? $this->queries->money($pay) : '—', __('Supplier bills'), $pay !== null ? 'watch' : 'pending', 'Accounting'),
            $this->kpiCard(__('Gross margin'), '—', __('Pending job costing'), 'pending', 'Accounting'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function hrKpis(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('employees')) {
            return [
                $this->kpiCard(__('Employee count'), '—', __('Company roster'), 'pending', 'HR'),
                $this->kpiCard(__('Active employees'), '—', __('Active roster'), 'pending', 'HR'),
                $this->kpiCard(__('Attendance rate'), '—', __('Pending source'), 'pending', 'HR'),
                $this->kpiCard(__('Payroll cost'), '—', __('Pending source'), 'pending', 'HR'),
            ];
        }

        $count = (string) $this->queries->countEmployees($scope);

        return [
            $this->kpiCard(__('Employee count'), $count, __('Company roster'), 'good', 'HR'),
            $this->kpiCard(__('Active employees'), $count, __('Active roster'), 'good', 'HR'),
            $this->kpiCard(__('Attendance rate'), '—', __('Pending source'), 'pending', 'HR'),
            $this->kpiCard(__('Payroll cost'), '—', __('Pending source'), 'pending', 'HR'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function kpiCard(string $name, string $value, string $hint, string $status, string $source): array
    {
        return [
            'name' => $name,
            'value' => $value,
            'hint' => $hint,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'source' => $source,
        ];
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'good' => __('Good'),
            'watch' => __('Watch'),
            'critical' => __('Critical'),
            'pending' => __('Pending source'),
            default => ucfirst($status),
        };
    }
}
