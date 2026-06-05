<?php

namespace App\Support\Production\Reports\Exports;

use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use App\Support\Production\Reports\CostingReportPresenter;
use App\Support\Production\Reports\CostingReportQueries;
use App\Support\Production\Reports\CostingReportScope;

class CostingReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected CostingReportQueries $queries,
        protected CostingReportPresenter $presenter,
    ) {}

    public function module(): string
    {
        return 'costing';
    }

    public function columns(CommercialReportExport $export): array
    {
        return $this->presenter->columnsForTab($export->tab ?: 'job_profitability');
    }

    public function rows(CommercialReportExport $export): \Generator
    {
        $scope = $this->buildScope($export);

        return CommercialReportExportPaginator::yieldPages(
            fn (int $page) => $this->mapPaginator($this->queries->paginateForTab($this->queries->withPage($scope, $page)))
        );
    }

    public function title(CommercialReportExport $export): string
    {
        $tabs = collect($this->presenter->tabs())->keyBy('key');

        return (string) ($tabs->get($export->tab)['label'] ?? __('Costing Reports'));
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $payload = $export->scope_payload ?? [];

        return collect([
            isset($payload['from_date'], $payload['to_date'])
                ? __('Period: :from – :to', ['from' => $payload['from_date'], 'to' => $payload['to_date']])
                : null,
            ! empty($payload['branch_id']) ? __('Branch filter applied') : null,
            ! empty($payload['customer_id']) ? __('Customer filter applied') : null,
            ! empty($payload['production_type']) ? __('Product/department filter applied') : null,
            ! empty($payload['job_card_id']) ? __('Job filter applied') : null,
        ])->filter()->implode(' · ');
    }

    protected function buildScope(CommercialReportExport $export): CostingReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new CostingReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: isset($payload['branch_id']) && $payload['branch_id'] !== '' ? (int) $payload['branch_id'] : null,
            fromDate: (string) ($payload['from_date'] ?? now()->startOfMonth()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            customerId: isset($payload['customer_id']) && $payload['customer_id'] !== '' ? (int) $payload['customer_id'] : null,
            productionType: isset($payload['production_type']) && $payload['production_type'] !== '' ? (string) $payload['production_type'] : null,
            jobCardId: isset($payload['job_card_id']) && $payload['job_card_id'] !== '' ? (int) $payload['job_card_id'] : null,
            search: (string) ($payload['search'] ?? ''),
            tab: (string) ($export->tab ?: 'job_profitability'),
            page: 1,
        );
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function mapPaginator(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())
            ->map(fn ($row) => array_values((array) $row))
            ->all();
    }
}
