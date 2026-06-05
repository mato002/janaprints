<?php

namespace App\Support\Procurement\Performance\Exports;

use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use App\Support\Procurement\Performance\SupplierPerformancePresenter;
use App\Support\Procurement\Performance\SupplierPerformanceQueries;
use App\Support\Procurement\Performance\SupplierPerformanceScope;
use Generator;

class SupplierPerformanceExporter implements CommercialReportExporter
{
    public function __construct(
        protected SupplierPerformanceQueries $queries,
        protected SupplierPerformancePresenter $presenter,
    ) {}

    public function module(): string
    {
        return 'supplier_performance';
    }

    public function columns(CommercialReportExport $export): array
    {
        $tab = $export->tab ?: 'scorecard';

        return match ($tab) {
            'delivery' => ['PO Number', 'Supplier', 'Expected Date', 'Actual Date', 'Variance', 'Days Late', 'Days Early'],
            'quality' => ['Supplier', 'Items Received', 'Items Rejected', 'Defect Rate', 'Return Rate'],
            'spend' => ['Supplier', 'Spend', 'Orders', 'Average Order Value'],
            'trends' => ['Period', 'Orders', 'Spend', 'On-Time %'],
            'rankings' => ['Ranking', 'Supplier', 'Metric'],
            default => ['Supplier', 'Overall Score', 'Grade', 'Purchase Count', 'On-Time %', 'Quality %', 'Avg Delivery', 'Spend'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'delivery' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapDelivery($this->queries->paginateDeliveryAnalysis($this->withPage($scope, $page)))
            ),
            'quality' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapQuality($this->queries->paginateQualityAnalysis($this->withPage($scope, $page)))
            ),
            'spend' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapSpend($this->queries->paginateSpendAnalysis($this->withPage($scope, $page)))
            ),
            'trends' => CommercialReportExportPaginator::yieldArray(
                collect($this->queries->performanceTrendSeries($scope)['monthly'] ?? [])->map(fn (array $point) => [
                    $point['label'],
                    (string) $point['orders'],
                    $this->queries->money($point['spend']),
                    $this->queries->percent($point['on_time_percent']),
                ])->all()
            ),
            'rankings' => CommercialReportExportPaginator::yieldArray(
                $this->mapRankings($this->queries->rankings($scope))
            ),
            default => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapScorecard($this->queries->paginateScorecard($this->withPage($scope, $page)))
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        $tabs = collect($this->presenter->tabs())->keyBy('key');

        return (string) ($tabs->get($export->tab)['label'] ?? __('Supplier Performance'));
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $payload = $export->scope_payload ?? [];

        return collect([
            isset($payload['from_date'], $payload['to_date'])
                ? __('Period: :from – :to', ['from' => $payload['from_date'], 'to' => $payload['to_date']])
                : null,
            ! empty($payload['branch_id']) ? __('Branch filter applied') : null,
            ! empty($payload['supplier_id']) ? __('Supplier filter applied') : null,
        ])->filter()->implode(' · ');
    }

    protected function buildScope(CommercialReportExport $export): SupplierPerformanceScope
    {
        $payload = $export->scope_payload ?? [];

        return new SupplierPerformanceScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: isset($payload['branch_id']) && $payload['branch_id'] !== '' ? (int) $payload['branch_id'] : null,
            fromDate: (string) ($payload['from_date'] ?? now()->startOfMonth()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            supplierId: isset($payload['supplier_id']) && $payload['supplier_id'] !== '' ? (int) $payload['supplier_id'] : null,
            warehouseId: isset($payload['warehouse_id']) && $payload['warehouse_id'] !== '' ? (int) $payload['warehouse_id'] : null,
            categoryId: isset($payload['category_id']) && $payload['category_id'] !== '' ? (int) $payload['category_id'] : null,
            search: (string) ($payload['search'] ?? ''),
            tab: (string) ($export->tab ?: 'scorecard'),
            topLimit: (int) ($payload['top_limit'] ?? 10),
            page: 1,
        );
    }

    protected function withPage(SupplierPerformanceScope $scope, int $page): SupplierPerformanceScope
    {
        return $this->queries->withPage($scope, $page);
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function mapScorecard(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(fn (array $row) => [
            $row['supplier'],
            $row['overall_score'] === null ? '—' : (string) $row['overall_score'],
            $row['grade'],
            (string) $row['purchase_count'],
            $this->queries->percent($row['on_time_percent']),
            $this->queries->percent($row['quality_acceptance_percent']),
            $this->queries->days($row['average_delivery_time']),
            $this->queries->money((float) $row['total_purchase_value']),
        ])->all();
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function mapDelivery(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(fn (array $row) => [
            $row['po_number'],
            $row['supplier'],
            $row['expected_date'],
            $row['actual_date'],
            $row['variance'],
            $row['days_late'],
            $row['days_early'],
        ])->all();
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function mapQuality(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(fn (array $row) => [
            $row['supplier'],
            number_format($row['items_received'], 2),
            number_format($row['items_rejected'], 2),
            $this->queries->percent($row['defect_rate']),
            $this->queries->percent($row['return_rate']),
        ])->all();
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function mapSpend(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(fn (array $row) => [
            $row['supplier'],
            $this->queries->money($row['spend']),
            (string) $row['orders'],
            $this->queries->money($row['average_order_value']),
        ])->all();
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $rankings
     * @return list<array<int, mixed>>
     */
    protected function mapRankings(array $rankings): array
    {
        $rows = [];

        foreach ($rankings['top_suppliers'] as $row) {
            $rows[] = [__('Top Suppliers'), $row['supplier'], (string) ($row['overall_score'] ?? '—').' ('.$row['grade'].')'];
        }

        foreach ($rankings['most_reliable'] as $row) {
            $rows[] = [__('Most Reliable'), $row['supplier'], $this->queries->percent($row['on_time_percent'])];
        }

        foreach ($rankings['fastest_delivery'] as $row) {
            $rows[] = [__('Fastest Delivery'), $row['supplier'], $this->queries->days($row['average_delivery_time'])];
        }

        foreach ($rankings['best_price'] as $row) {
            $rows[] = [__('Best Price'), $row['supplier'], (string) $row['price_score']];
        }

        foreach ($rankings['highest_spend'] as $row) {
            $rows[] = [__('Highest Spend'), $row['supplier'], $this->queries->money($row['spend'])];
        }

        return $rows;
    }
}
