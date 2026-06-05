<?php

namespace App\Support\Commercial\Reports\Exports\Exporters;

use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\CommercialSalesOrderReportQueries;
use App\Support\Commercial\Reports\CommercialSalesOrderReportScope;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use Generator;

class SalesOrderReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected CommercialSalesOrderReportQueries $queries,
    ) {}

    public function module(): string
    {
        return 'sales_orders';
    }

    public function columns(CommercialReportExport $export): array
    {
        return match ($export->tab) {
            'open', 'pending', 'completed', 'cancelled', 'awaiting_production' => [
                'Order', 'Customer', 'Branch', 'Salesperson', 'Status', 'Order Date', 'Required Date', 'Value', 'Age (days)',
            ],
            'by_customer' => ['Customer', 'Orders', 'Value', 'Average', 'Open'],
            'by_branch' => ['Branch', 'Orders', 'Value', 'Average', 'Open'],
            'by_salesperson' => ['Salesperson', 'Orders', 'Value', 'Average', 'Completed'],
            'aging' => ['Age Bucket', 'Orders', 'Value'],
            'value_analysis' => ['Value Bucket', 'Orders', 'Total Value', 'Average'],
            'from_quotations' => ['Order', 'Quotation', 'Quote Date', 'Customer', 'Branch', 'Salesperson', 'Status', 'Order Date', 'Value'],
            default => ['Metric', 'Value'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'open' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateOpenOrders($this->withPage($scope, $page))
            ),
            'pending' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginatePendingOrders($this->withPage($scope, $page))
            ),
            'completed' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateCompletedOrders($this->withPage($scope, $page))
            ),
            'cancelled' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateCancelledOrders($this->withPage($scope, $page))
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
            'aging' => CommercialReportExportPaginator::yieldArray($this->queries->orderAgingBuckets($scope)),
            'value_analysis' => CommercialReportExportPaginator::yieldArray($this->queries->orderValueBuckets($scope)),
            'awaiting_production' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateAwaitingProduction($this->withPage($scope, $page))
            ),
            'from_quotations' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateFromQuotations($this->withPage($scope, $page))
            ),
            default => CommercialReportExportPaginator::yieldArray(
                collect($this->queries->summaryMetrics($scope))->map(fn ($value, $key) => [
                    'metric' => ucfirst(str_replace('_', ' ', (string) $key)),
                    'value' => is_float($value) && str_contains((string) $key, 'value')
                        ? $this->queries->money($value)
                        : (is_float($value) ? $value.'%' : (string) $value),
                ])->values()->all()
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        return __('Sales Order Report');
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $scope = $this->buildScope($export);

        return __('Period: :from — :to', ['from' => $scope->fromDate, 'to' => $scope->toDate]);
    }

    protected function buildScope(CommercialReportExport $export): CommercialSalesOrderReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new CommercialSalesOrderReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: $payload['branch_id'] ?? null,
            fromDate: (string) ($payload['from_date'] ?? now()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            customerId: $payload['customer_id'] ?? null,
            salespersonId: $payload['salesperson_id'] ?? null,
            status: $payload['status'] ?? null,
            search: (string) ($payload['search'] ?? ''),
            tab: $export->tab,
        );
    }

    protected function withPage(CommercialSalesOrderReportScope $scope, int $page): CommercialSalesOrderReportScope
    {
        return new CommercialSalesOrderReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            customerId: $scope->customerId,
            salespersonId: $scope->salespersonId,
            status: $scope->status,
            search: $scope->search,
            tab: $scope->tab,
            page: $page,
        );
    }
}
