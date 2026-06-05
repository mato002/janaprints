<?php

namespace App\Support\Commercial\Reports\Exports\Exporters;

use App\Enums\CustomerStatus;
use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\CommercialCustomerReportQueries;
use App\Support\Commercial\Reports\CommercialCustomerReportScope;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use Generator;
use Illuminate\Support\Facades\Schema;

class CustomerReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected CommercialCustomerReportQueries $queries,
    ) {}

    public function module(): string
    {
        return 'customers';
    }

    public function columns(CommercialReportExport $export): array
    {
        return match ($export->tab) {
            'new' => ['Customer', 'Code', 'Type', 'Status', 'Orders', 'Revenue', 'Open Quotes', 'Created'],
            'active', 'inactive' => ['Customer', 'Code', 'Type', 'Status', 'Orders', 'Revenue', 'Last Order', 'Open Quotes'],
            'revenue', 'lifetime' => ['Customer', 'Orders', 'Revenue', 'Last Order', 'Lifetime Value'],
            'activity' => Schema::hasTable('customer_activities')
                ? ['Customer', 'Type', 'Subject', 'Date']
                : ['Customer', 'Type', 'Subject', 'Date', 'Value'],
            'top' => ['Customer', 'Orders', 'Revenue', 'Lifetime Value'],
            'growth' => ['Period', 'New Customers', 'Growth %'],
            'no_recent_orders' => ['Customer', 'Code', 'Status', 'Last Order', 'Days Inactive'],
            'by_branch' => ['Branch', 'Customers', 'Active', 'Inactive', 'Revenue'],
            'by_salesperson' => ['Salesperson', 'Customers', 'Orders', 'Revenue', 'Average Value'],
            default => ['Metric', 'Value'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'new' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateCustomerList($this->withPage($scope, $page), newOnly: true)
            ),
            'active' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateCustomerList($this->withPage($scope, $page), statusFilter: CustomerStatus::Active->value)
            ),
            'inactive' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateCustomerList($this->withPage($scope, $page), statusFilter: CustomerStatus::Inactive->value)
            ),
            'revenue' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateRevenue($this->withPage($scope, $page))
            ),
            'lifetime' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateLifetimeValue($this->withPage($scope, $page))
            ),
            'activity' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateActivity($this->withPage($scope, $page))
            ),
            'top' => CommercialReportExportPaginator::yieldArray($this->queries->topCustomers($scope)->values()->all()),
            'growth' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateGrowth($this->withPage($scope, $page))
            ),
            'no_recent_orders' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateWithoutRecentOrders($this->withPage($scope, $page))
            ),
            'by_branch' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByBranch($this->withPage($scope, $page))
            ),
            'by_salesperson' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateBySalesperson($this->withPage($scope, $page))
            ),
            default => CommercialReportExportPaginator::yieldArray(
                collect([
                    'total' => __('Total Customers'),
                    'new' => __('New Customers'),
                    'active' => __('Active Customers'),
                    'inactive' => __('Inactive Customers'),
                    'repeat' => __('Repeat Customers'),
                    'growth' => __('Customer Growth %'),
                    'average_value' => __('Average Customer Value'),
                    'open_quotes' => __('Customers With Open Quotes'),
                    'open_orders' => __('Customers With Open Orders'),
                ])->map(function (string $label, string $key) use ($scope) {
                    $metrics = $this->queries->summaryMetrics($scope);
                    $value = $metrics[$key] ?? 0;

                    return [
                        'metric' => $label,
                        'value' => in_array($key, ['average_value'], true)
                            ? $this->queries->money((float) $value)
                            : ($key === 'growth' && $value !== null ? $value.'%' : (string) $value),
                    ];
                })->values()->all()
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        return __('Customer Report');
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $scope = $this->buildScope($export);

        return __('Period: :from — :to', ['from' => $scope->fromDate, 'to' => $scope->toDate]);
    }

    protected function buildScope(CommercialReportExport $export): CommercialCustomerReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new CommercialCustomerReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: $payload['branch_id'] ?? null,
            fromDate: (string) ($payload['from_date'] ?? now()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            customerType: $payload['customer_type'] ?? null,
            status: $payload['status'] ?? null,
            salespersonId: $payload['salesperson_id'] ?? null,
            activityStatus: $payload['activity_status'] ?? null,
            search: (string) ($payload['search'] ?? ''),
            tab: $export->tab,
            topLimit: (int) ($payload['top_limit'] ?? 10),
        );
    }

    protected function withPage(CommercialCustomerReportScope $scope, int $page): CommercialCustomerReportScope
    {
        return new CommercialCustomerReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            customerType: $scope->customerType,
            status: $scope->status,
            salespersonId: $scope->salespersonId,
            activityStatus: $scope->activityStatus,
            search: $scope->search,
            tab: $scope->tab,
            topLimit: $scope->topLimit,
            page: $page,
        );
    }
}
