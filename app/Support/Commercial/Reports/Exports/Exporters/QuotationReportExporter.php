<?php

namespace App\Support\Commercial\Reports\Exports\Exporters;

use App\Enums\QuotationStatus;
use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\CommercialQuotationReportQueries;
use App\Support\Commercial\Reports\CommercialQuotationReportScope;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use Generator;

class QuotationReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected CommercialQuotationReportQueries $queries,
    ) {}

    public function module(): string
    {
        return 'quotations';
    }

    public function columns(CommercialReportExport $export): array
    {
        return match ($export->tab) {
            'open', 'expired', 'accepted', 'rejected' => ['Quote', 'Customer', 'Date', 'Valid Until', 'Value', 'Status', 'Salesperson'],
            'value_analysis' => ['Value Band', 'Quotes', 'Total Value', 'Average Value'],
            'aging' => ['Age Band', 'Open Quotes', 'Value'],
            'by_customer' => ['Customer', 'Quotes', 'Total Value', 'Average Value', 'Win Rate'],
            'by_salesperson' => ['Salesperson', 'Quotes', 'Total Value', 'Average Value', 'Win Rate'],
            'by_branch' => ['Branch', 'Quotes', 'Total Value', 'Won', 'Win Rate'],
            'win_rate' => ['Metric', 'Value'],
            default => ['Metric', 'Value'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'open' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateQuotations($this->withPage($scope, $page), 'open')
            ),
            'expired' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateQuotations($this->withPage($scope, $page), 'expired')
            ),
            'accepted' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateQuotations($this->withPage($scope, $page), [QuotationStatus::Accepted->value, QuotationStatus::Converted->value])
            ),
            'rejected' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateQuotations($this->withPage($scope, $page), QuotationStatus::Rejected)
            ),
            'value_analysis' => CommercialReportExportPaginator::yieldArray($this->queries->valueAnalysis($scope)),
            'aging' => CommercialReportExportPaginator::yieldArray($this->queries->agingBuckets($scope)),
            'by_customer' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByCustomer($this->withPage($scope, $page))
            ),
            'by_salesperson' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateBySalesperson($this->withPage($scope, $page))
            ),
            'by_branch' => CommercialReportExportPaginator::yieldArray($this->queries->branchBreakdown($scope)),
            'win_rate' => CommercialReportExportPaginator::yieldArray(
                collect($this->queries->winRateAnalysis($scope))
                    ->map(fn ($value, $key) => ['metric' => (string) $key, 'value' => (string) $value])
                    ->values()->all()
            ),
            default => CommercialReportExportPaginator::yieldArray(
                collect($this->queries->summaryMetrics($scope))
                    ->map(fn ($value, $key) => ['metric' => (string) $key, 'value' => (string) $value])
                    ->values()->all()
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        return __('Quotation Report');
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $scope = $this->buildScope($export);

        return __('Period: :from — :to', ['from' => $scope->fromDate, 'to' => $scope->toDate]);
    }

    protected function buildScope(CommercialReportExport $export): CommercialQuotationReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new CommercialQuotationReportScope(
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

    protected function withPage(CommercialQuotationReportScope $scope, int $page): CommercialQuotationReportScope
    {
        return new CommercialQuotationReportScope(
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
