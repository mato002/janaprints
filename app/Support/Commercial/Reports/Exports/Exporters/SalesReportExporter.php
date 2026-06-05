<?php

namespace App\Support\Commercial\Reports\Exports\Exporters;

use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\CommercialSalesReportQueries;
use App\Support\Commercial\Reports\CommercialSalesReportScope;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use Generator;

class SalesReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected CommercialSalesReportQueries $queries,
    ) {}

    public function module(): string
    {
        return 'sales';
    }

    public function columns(CommercialReportExport $export): array
    {
        return match ($export->tab) {
            'by_day' => ['Date', 'Orders', 'Revenue', 'Average Order Value', 'Trend'],
            'by_week' => ['Week', 'Orders', 'Revenue', 'Customers', 'Growth %'],
            'by_month' => ['Month', 'Orders', 'Revenue', 'Customers', 'Growth %'],
            'by_customer' => ['Customer', 'Orders', 'Revenue', 'Last Order', 'Average Order', 'Lifetime Value'],
            'by_branch' => ['Branch', 'Orders', 'Revenue', 'Customers', 'Average Order'],
            'by_salesperson' => ['Salesperson', 'Orders', 'Revenue', 'Customers', 'Average Order', 'Conversion %'],
            'top_customers' => ['Customer', 'Orders', 'Revenue', 'Lifetime Value'],
            default => ['Metric', 'Value'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'by_day' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByDay($this->withPage($scope, $page))
            ),
            'by_week' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByWeek($this->withPage($scope, $page))
            ),
            'by_month' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByMonth($this->withPage($scope, $page))
            ),
            'by_customer' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByCustomer($this->withPage($scope, $page))
            ),
            'by_branch' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByBranch($this->withPage($scope, $page))
            ),
            'by_salesperson' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateBySalesperson($this->withPage($scope, $page))
            ),
            'top_customers' => CommercialReportExportPaginator::yieldArray(
                $this->queries->topCustomers($scope)->values()->all()
            ),
            default => CommercialReportExportPaginator::yieldArray(
                collect([
                    'orders' => __('Orders'),
                    'revenue' => __('Revenue'),
                    'average_order_value' => __('Average Order Value'),
                    'customer_count' => __('Customer Count'),
                ])->map(function (string $label, string $key) use ($scope) {
                    $metrics = $this->queries->summaryMetrics($scope);
                    $value = $metrics[$key] ?? 0;

                    return [
                        'metric' => $label,
                        'value' => in_array($key, ['revenue', 'average_order_value'], true)
                            ? $this->queries->money((float) $value)
                            : (string) $value,
                    ];
                })->values()->all()
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        return __('Sales Report');
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $scope = $this->buildScope($export);

        return __('Period: :from — :to', ['from' => $scope->fromDate, 'to' => $scope->toDate]);
    }

    protected function buildScope(CommercialReportExport $export): CommercialSalesReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new CommercialSalesReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: $payload['branch_id'] ?? null,
            fromDate: (string) ($payload['from_date'] ?? now()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            customerId: $payload['customer_id'] ?? null,
            salespersonId: $payload['salesperson_id'] ?? null,
            status: $payload['status'] ?? null,
            search: (string) ($payload['search'] ?? ''),
            tab: $export->tab,
            topLimit: (int) ($payload['top_limit'] ?? 10),
            topBy: (string) ($payload['top_by'] ?? 'revenue'),
        );
    }

    protected function withPage(CommercialSalesReportScope $scope, int $page): CommercialSalesReportScope
    {
        return new CommercialSalesReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            customerId: $scope->customerId,
            salespersonId: $scope->salespersonId,
            status: $scope->status,
            search: $scope->search,
            tab: $scope->tab,
            topLimit: $scope->topLimit,
            topBy: $scope->topBy,
            page: $page,
        );
    }
}
