<?php

namespace App\Support\Commercial\Reports\Exports\Exporters;

use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\CommercialConversionReportQueries;
use App\Support\Commercial\Reports\CommercialConversionReportScope;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use Generator;

class ConversionReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected CommercialConversionReportQueries $queries,
    ) {}

    public function module(): string
    {
        return 'conversion';
    }

    public function columns(CommercialReportExport $export): array
    {
        return match ($export->tab) {
            'branch' => ['Branch', 'Leads', 'Quotes', 'Orders', 'Production', 'Dispatch', 'Delivered', 'Lead→Quote', 'Quote→Order'],
            'salesperson' => ['Salesperson', 'Leads', 'Quotes', 'Orders', 'Lead→Quote', 'Quote→Order'],
            default => ['Stage', 'Count', 'Conversion', 'Drop-off'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'branch' => CommercialReportExportPaginator::yieldArray($this->queries->branchConversionRows($scope)),
            'salesperson' => CommercialReportExportPaginator::yieldArray($this->queries->salespersonConversionRows($scope)),
            default => CommercialReportExportPaginator::yieldArray($this->queries->dropOffTable($scope)),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        return __('Conversion Report');
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $scope = $this->buildScope($export);

        return __('Period: :from — :to', ['from' => $scope->fromDate, 'to' => $scope->toDate]);
    }

    protected function buildScope(CommercialReportExport $export): CommercialConversionReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new CommercialConversionReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: $payload['branch_id'] ?? null,
            fromDate: (string) ($payload['from_date'] ?? now()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            salespersonId: $payload['salesperson_id'] ?? null,
            leadSourceId: $payload['lead_source_id'] ?? null,
            customerType: $payload['customer_type'] ?? null,
            status: $payload['status'] ?? null,
            search: (string) ($payload['search'] ?? ''),
            tab: $export->tab,
        );
    }
}
