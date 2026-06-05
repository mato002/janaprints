<?php

namespace App\Support\Commercial\Reports\Exports\Exporters;

use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\CommercialPosReportQueries;
use App\Support\Commercial\Reports\CommercialPosReportScope;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use Generator;

class PosReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected CommercialPosReportQueries $queries,
    ) {}

    public function module(): string
    {
        return 'pos';
    }

    public function columns(CommercialReportExport $export): array
    {
        return match ($export->tab) {
            'sales_by_branch' => ['Branch', 'Sales', 'Revenue', 'Avg Sale'],
            'sales_by_day' => ['Day', 'Sales', 'Revenue'],
            'sales_by_hour' => ['Hour', 'Sales', 'Revenue'],
            'returns_analysis' => ['Day', 'Returns', 'Value', 'Rate'],
            'refund_analysis' => ['Payment Method', 'Refunds', 'Value', 'Avg Refund'],
            'session_performance' => ['Session', 'Branch', 'Cashier', 'Status', 'Sales', 'Revenue', 'Variance'],
            'payment_method_analysis' => ['Payment Method', 'Sales', 'Collected', 'Share', 'Avg Payment'],
            default => ['Cashier', 'Sales', 'Revenue', 'Avg Sale'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'sales_by_branch' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateSalesByBranch($this->withPage($scope, $page))
            ),
            'sales_by_day' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateSalesByDay($this->withPage($scope, $page))
            ),
            'sales_by_hour' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateSalesByHour($this->withPage($scope, $page))
            ),
            'returns_analysis' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateReturnsAnalysis($this->withPage($scope, $page))
            ),
            'refund_analysis' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateRefundAnalysis($this->withPage($scope, $page))
            ),
            'session_performance' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateSessionPerformance($this->withPage($scope, $page))
            ),
            'payment_method_analysis' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginatePaymentMethodAnalysis($this->withPage($scope, $page))
            ),
            default => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateSalesByCashier($this->withPage($scope, $page))
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        return __('POS Intelligence Report');
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $scope = $this->buildScope($export);

        return __('Period: :from — :to', ['from' => $scope->fromDate, 'to' => $scope->toDate]);
    }

    protected function buildScope(CommercialReportExport $export): CommercialPosReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new CommercialPosReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: $payload['branch_id'] ?? null,
            fromDate: (string) ($payload['from_date'] ?? now()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            cashierId: $payload['cashier_id'] ?? null,
            paymentMethod: $payload['payment_method'] ?? null,
            status: $payload['status'] ?? null,
            search: (string) ($payload['search'] ?? ''),
            tab: $export->tab,
        );
    }

    protected function withPage(CommercialPosReportScope $scope, int $page): CommercialPosReportScope
    {
        return new CommercialPosReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            cashierId: $scope->cashierId,
            paymentMethod: $scope->paymentMethod,
            status: $scope->status,
            search: $scope->search,
            tab: $scope->tab,
            page: $page,
        );
    }
}
