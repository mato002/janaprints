<?php

namespace App\Support\Reports;

use App\Enums\InventoryMovementType;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryValuation;
use App\Models\Inventory\Warehouse;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Inventory360Presenter
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
        $resolved = $this->scopeResolver->resolve($request, includeWarehouse: true);
        $scope = $resolved['scope'];

        $warehouses = $this->queries->hasTable('warehouses')
            ? Warehouse::query()->where('company_id', $scope->companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return [
            'title' => __('Inventory 360'),
            'description' => __('Stock health, valuation, and movement intelligence.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'warehouses' => $warehouses,
            'can_export' => $resolved['can_export'],
            'sections' => [
                $this->valueSummary($scope),
                $this->stockHealth($scope),
                $this->movementIntelligence($scope),
                $this->topItems($scope),
                $this->warehouseIntelligence($scope),
                $this->reorderIntelligence($scope),
                $this->consumptionSection($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function valueSummary(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('inventory_valuations')) {
            return $this->pendingSection(__('Inventory Value Summary'));
        }

        $total = $this->queries->inventoryValue($scope) ?? 0.0;

        $byBranch = DB::table('inventory_valuations')
            ->join('branches', 'branches.id', '=', 'inventory_valuations.branch_id')
            ->where('inventory_valuations.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('inventory_valuations.branch_id', $scope->branchId))
            ->select('branches.name', DB::raw('SUM(inventory_valuations.quantity_on_hand * inventory_valuations.average_unit_cost) as value'))
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        $byWarehouse = DB::table('inventory_valuations')
            ->join('warehouses', 'warehouses.id', '=', 'inventory_valuations.warehouse_id')
            ->where('inventory_valuations.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('inventory_valuations.branch_id', $scope->branchId))
            ->when($scope->warehouseId, fn ($q) => $q->where('inventory_valuations.warehouse_id', $scope->warehouseId))
            ->select('warehouses.name', DB::raw('SUM(inventory_valuations.quantity_on_hand * inventory_valuations.average_unit_cost) as value'))
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        $byCategory = $this->queries->hasTable('inventory_items') && $this->queries->hasTable('inventory_categories')
            ? DB::table('inventory_valuations')
                ->join('inventory_items', 'inventory_items.id', '=', 'inventory_valuations.inventory_item_id')
                ->leftJoin('inventory_categories', 'inventory_categories.id', '=', 'inventory_items.inventory_category_id')
                ->where('inventory_valuations.company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('inventory_valuations.branch_id', $scope->branchId))
                ->selectRaw('COALESCE(inventory_categories.name, ?) as name', [__('Uncategorized')])
                ->selectRaw('SUM(inventory_valuations.quantity_on_hand * inventory_valuations.average_unit_cost) as value')
                ->groupBy('inventory_categories.id', 'inventory_categories.name')
                ->orderByDesc('value')
                ->limit(10)
                ->get()
            : collect();

        return [
            'type' => 'split',
            'title' => __('Inventory Value Summary'),
            'kpis' => [
                $this->kpi(__('Total Inventory Value'), $this->queries->money($total), 'currency-dollar'),
            ],
            'tables' => [
                $this->tableSection(__('By Branch'), [__('Branch'), __('Value')], $byBranch->map(fn ($r) => ['name' => $r->name, 'value' => $this->queries->money((float) $r->value)])->all()),
                $this->tableSection(__('By Warehouse'), [__('Warehouse'), __('Value')], $byWarehouse->map(fn ($r) => ['name' => $r->name, 'value' => $this->queries->money((float) $r->value)])->all()),
                $this->tableSection(__('By Category'), [__('Category'), __('Value')], $byCategory->map(fn ($r) => ['name' => $r->name, 'value' => $this->queries->money((float) $r->value)])->all()),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function stockHealth(IntelligenceScope $scope): array
    {
        $low = $this->queries->countLowStockAlerts($scope);
        $kpis = [
            $this->kpi(__('Low Stock Alerts'), (string) $low, 'exclamation'),
        ];

        if (! $this->queries->hasTable('inventory_valuations')) {
            $kpis[] = $this->kpi(__('Out of Stock'), '—', 'cube', pending: true);
            $kpis[] = $this->kpi(__('Negative Stock'), '—', 'exclamation', pending: true);
            $kpis[] = $this->kpi(__('Zero Stock'), '—', 'inbox', pending: true);
            $kpis[] = $this->kpi(__('Without Average Cost'), '—', 'currency-dollar', pending: true);

            return $this->kpiSection(__('Stock Health'), $kpis);
        }

        $base = DB::table('inventory_valuations')->where('company_id', $scope->companyId);
        if ($scope->branchId) {
            $base->where('branch_id', $scope->branchId);
        }

        $out = (clone $base)->where('quantity_on_hand', '<=', 0)->count();
        $negative = (clone $base)->where('quantity_on_hand', '<', 0)->count();
        $zero = (clone $base)->where('quantity_on_hand', '=', 0)->count();
        $noCost = (clone $base)->where('average_unit_cost', '<=', 0)->count();

        $kpis[] = $this->kpi(__('Out of Stock'), (string) $out, 'cube');
        $kpis[] = $this->kpi(__('Negative Stock'), (string) $negative, 'exclamation');
        $kpis[] = $this->kpi(__('Zero Stock'), (string) $zero, 'inbox');
        $kpis[] = $this->kpi(__('Without Average Cost'), (string) $noCost, 'currency-dollar');

        return $this->kpiSection(__('Stock Health'), $kpis);
    }

    /**
     * @return array<string, mixed>
     */
    protected function movementIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('inventory_movements')) {
            return $this->pendingSection(__('Movement Intelligence'));
        }

        return $this->kpiSection(__('Movement Intelligence'), [
            $this->kpi(__('Receipts'), (string) $this->queries->countMovementInPeriod($scope, InventoryMovementType::Receipt), 'inbox'),
            $this->kpi(__('Issues'), (string) $this->queries->countMovementInPeriod($scope, InventoryMovementType::Issue), 'cube'),
            $this->kpi(__('Adjustments'), (string) $this->queries->countMovementInPeriod($scope, InventoryMovementType::Adjustment), 'document-text'),
            $this->kpi(__('Transfers'), (string) (
                $this->queries->countMovementInPeriod($scope, InventoryMovementType::TransferIn)
                + $this->queries->countMovementInPeriod($scope, InventoryMovementType::TransferOut)
            ), 'truck'),
            $this->kpi(__('Consumption'), (string) $this->queries->countMovementInPeriod($scope, InventoryMovementType::ProductionConsumption), 'cog'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function topItems(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('inventory_movements') || ! $this->queries->hasTable('inventory_items')) {
            return $this->pendingSection(__('Top Items'));
        }

        $consumed = $this->queries->scoped(InventoryMovement::class, $scope)
            ->where('movement_type', InventoryMovementType::ProductionConsumption)
            ->whereDate('movement_date', '>=', $scope->fromDate)
            ->whereDate('movement_date', '<=', $scope->toDate)
            ->select('inventory_item_id', DB::raw('SUM(ABS(quantity)) as qty'))
            ->groupBy('inventory_item_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $issued = $this->queries->scoped(InventoryMovement::class, $scope)
            ->where('movement_type', InventoryMovementType::Issue)
            ->whereDate('movement_date', '>=', $scope->fromDate)
            ->whereDate('movement_date', '<=', $scope->toDate)
            ->select('inventory_item_id', DB::raw('SUM(ABS(quantity)) as qty'))
            ->groupBy('inventory_item_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->pluck('qty', 'inventory_item_id');

        $itemNames = InventoryItem::query()
            ->whereIn('id', $consumed->pluck('inventory_item_id')->merge($issued->keys())->unique())
            ->pluck('item_name', 'id');

        $rows = $consumed->map(fn ($r) => [
            'name' => $itemNames[$r->inventory_item_id] ?? __('Item'),
            'metric' => __('Consumed'),
            'value' => number_format((float) $r->qty, 2),
        ])->all();

        $highValue = $this->queries->hasTable('inventory_valuations')
            ? DB::table('inventory_valuations')
                ->join('inventory_items', 'inventory_items.id', '=', 'inventory_valuations.inventory_item_id')
                ->where('inventory_valuations.company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('inventory_valuations.branch_id', $scope->branchId))
                ->select('inventory_items.item_name as name', DB::raw('SUM(inventory_valuations.quantity_on_hand * inventory_valuations.average_unit_cost) as value'))
                ->groupBy('inventory_items.id', 'inventory_items.item_name')
                ->orderByDesc('value')
                ->limit(5)
                ->get()
                ->map(fn ($r) => ['name' => $r->name, 'metric' => __('Value'), 'value' => $this->queries->money((float) $r->value)])
                ->all()
            : [];

        return $this->tableSection(
            __('Top Items'),
            [__('Item'), __('Metric'), __('Value')],
            array_merge($rows, $highValue),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function warehouseIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('warehouses') || ! $this->queries->hasTable('inventory_valuations')) {
            return $this->pendingSection(__('Warehouse Intelligence'));
        }

        $rows = DB::table('warehouses')
            ->leftJoin('branches', 'branches.id', '=', 'warehouses.branch_id')
            ->leftJoin('inventory_valuations', function ($join) use ($scope) {
                $join->on('inventory_valuations.warehouse_id', '=', 'warehouses.id')
                    ->where('inventory_valuations.company_id', '=', $scope->companyId);
            })
            ->where('warehouses.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('warehouses.branch_id', $scope->branchId))
            ->when($scope->warehouseId, fn ($q) => $q->where('warehouses.id', $scope->warehouseId))
            ->select(
                'warehouses.name as warehouse',
                'branches.name as branch',
                DB::raw('COUNT(DISTINCT inventory_valuations.inventory_item_id) as items'),
                DB::raw('COALESCE(SUM(inventory_valuations.quantity_on_hand), 0) as quantity'),
                DB::raw('COALESCE(SUM(inventory_valuations.quantity_on_hand * inventory_valuations.average_unit_cost), 0) as value'),
                DB::raw('SUM(CASE WHEN inventory_valuations.quantity_on_hand < 0 THEN 1 ELSE 0 END) as negative_count'),
            )
            ->groupBy('warehouses.id', 'warehouses.name', 'branches.name')
            ->orderBy('warehouses.name')
            ->get()
            ->map(fn ($r) => [
                'warehouse' => $r->warehouse,
                'branch' => $r->branch ?? '—',
                'items' => (string) $r->items,
                'quantity' => number_format((float) $r->quantity, 2),
                'value' => $this->queries->money((float) $r->value),
                'low_stock' => '—',
                'negative' => (string) $r->negative_count,
            ])
            ->all();

        return $this->tableSection(
            __('Warehouse Intelligence'),
            [__('Warehouse'), __('Branch'), __('Items'), __('Qty on Hand'), __('Value'), __('Low Stock'), __('Negative')],
            $rows,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function reorderIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('inventory_reorder_alerts')) {
            return $this->pendingSection(__('Reorder Intelligence'));
        }

        $open = (int) $this->queries->scoped(\App\Models\Inventory\InventoryReorderAlert::class, $scope)
            ->where('is_resolved', false)->count();

        $critical = (int) $this->queries->scoped(\App\Models\Inventory\InventoryReorderAlert::class, $scope)
            ->where('is_resolved', false)
            ->whereColumn('current_quantity', '<', 'reorder_level')
            ->count();

        return $this->kpiSection(__('Reorder Intelligence'), [
            $this->kpi(__('Open Reorder Alerts'), (string) $open, 'bell'),
            $this->kpi(__('Critical Alerts'), (string) $critical, 'exclamation'),
            $this->kpi(__('Below Reorder Level'), (string) $open, 'cube'),
            $this->kpi(__('Suggested Reorder Qty'), '—', 'clipboard-list', __('Pending source')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function consumptionSection(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_material_consumptions')) {
            return $this->pendingSection(__('Material Consumption'));
        }

        $count = (int) DB::table('production_material_consumptions')
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('consumed_at', '>=', $scope->fromDate)
            ->whereDate('consumed_at', '<=', $scope->toDate)
            ->count();

        return $this->kpiSection(__('Material Consumption'), [
            $this->kpi(__('Consumption Lines (period)'), (string) $count, 'cog'),
        ]);
    }
}
