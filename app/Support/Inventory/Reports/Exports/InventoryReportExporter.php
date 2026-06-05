<?php

namespace App\Support\Inventory\Reports\Exports;

use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use App\Support\Inventory\Reports\InventoryReportPresenter;
use App\Support\Inventory\Reports\InventoryReportQueries;
use App\Support\Inventory\Reports\InventoryReportScope;
use Generator;

class InventoryReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected InventoryReportQueries $queries,
        protected InventoryReportPresenter $presenter,
    ) {}

    public function module(): string
    {
        return 'inventory';
    }

    public function columns(CommercialReportExport $export): array
    {
        $tab = $export->tab ?: 'stock_on_hand';

        return match ($tab) {
            'low_stock' => ['Item', 'Min Level', 'Current Qty', 'Shortfall', 'Days Remaining'],
            'out_of_stock' => ['Item', 'Warehouse', 'Last Movement', 'Last Purchase'],
            'slow_moving' => ['Item', 'Last Sale', 'Last Consumption', 'Days Idle', 'Value Locked'],
            'dead_stock' => ['Item', 'Days Without Movement', 'Qty', 'Value'],
            'stock_aging' => ['Item', 'Warehouse', 'Last Receipt', 'Age (Days)', 'Qty', 'Value'],
            'warehouse_summary' => ['Warehouse', 'Items', 'Qty', 'Value'],
            default => ['Item', 'SKU', 'Category', 'Warehouse', 'Available Qty', 'Reserved Qty', 'On Hand Qty', 'Unit Cost', 'Inventory Value'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'low_stock' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapPaginator($this->queries->paginateLowStock($this->withPage($scope, $page)))
            ),
            'out_of_stock' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapPaginator($this->queries->paginateOutOfStock($this->withPage($scope, $page)))
            ),
            'slow_moving' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapPaginator($this->queries->paginateSlowMoving($this->withPage($scope, $page)))
            ),
            'dead_stock' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapPaginator($this->queries->paginateDeadStock($this->withPage($scope, $page)))
            ),
            'stock_aging' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapPaginator($this->queries->paginateStockAging($this->withPage($scope, $page)))
            ),
            'warehouse_summary' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapPaginator($this->queries->paginateWarehouseSummary($this->withPage($scope, $page)))
            ),
            default => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapPaginator($this->queries->paginateStockOnHand($this->withPage($scope, $page)))
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        $tabs = collect($this->presenter->tabs())->keyBy('key');

        return (string) ($tabs->get($export->tab)['label'] ?? __('Inventory Reports'));
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $payload = $export->scope_payload ?? [];

        return collect([
            isset($payload['from_date'], $payload['to_date'])
                ? __('Period: :from – :to', ['from' => $payload['from_date'], 'to' => $payload['to_date']])
                : null,
            ! empty($payload['branch_id']) ? __('Branch filter applied') : null,
            ! empty($payload['warehouse_id']) ? __('Warehouse filter applied') : null,
        ])->filter()->implode(' · ');
    }

    protected function buildScope(CommercialReportExport $export): InventoryReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new InventoryReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: isset($payload['branch_id']) && $payload['branch_id'] !== '' ? (int) $payload['branch_id'] : null,
            fromDate: (string) ($payload['from_date'] ?? now()->subDays(90)->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            warehouseId: isset($payload['warehouse_id']) && $payload['warehouse_id'] !== '' ? (int) $payload['warehouse_id'] : null,
            categoryId: isset($payload['category_id']) && $payload['category_id'] !== '' ? (int) $payload['category_id'] : null,
            supplierId: isset($payload['supplier_id']) && $payload['supplier_id'] !== '' ? (int) $payload['supplier_id'] : null,
            itemId: isset($payload['item_id']) && $payload['item_id'] !== '' ? (int) $payload['item_id'] : null,
            search: (string) ($payload['search'] ?? ''),
            tab: (string) ($export->tab ?: 'stock_on_hand'),
            page: 1,
        );
    }

    protected function withPage(InventoryReportScope $scope, int $page): InventoryReportScope
    {
        return $this->queries->withPage($scope, $page);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function mapPaginator(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())
            ->map(fn ($row) => array_values((array) $row))
            ->all();
    }
}
