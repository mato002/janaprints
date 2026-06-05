<?php

namespace App\Support\Inventory\Reports;

use App\Enums\InventoryMovementType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryReportQueries
{
    public const PER_PAGE = 25;

    public const SLOW_MOVING_DAYS = 90;

    public const DEAD_STOCK_DAYS = 180;

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }

    public function qty(float $amount): string
    {
        return number_format($amount, 2);
    }

    protected function valuationQuery(InventoryReportScope $scope): Builder
    {
        $query = DB::table('inventory_valuations as iv')
            ->join('inventory_items as ii', 'ii.id', '=', 'iv.inventory_item_id')
            ->join('warehouses as w', 'w.id', '=', 'iv.warehouse_id')
            ->where('iv.company_id', $scope->companyId);

        if ($this->hasTable('inventory_categories')) {
            $query->leftJoin('inventory_categories as ic', 'ic.id', '=', 'ii.inventory_category_id');
        }

        if ($scope->branchId !== null) {
            $query->where('iv.branch_id', $scope->branchId);
        }

        if ($scope->warehouseId !== null) {
            $query->where('iv.warehouse_id', $scope->warehouseId);
        }

        if ($scope->categoryId !== null) {
            $query->where('ii.inventory_category_id', $scope->categoryId);
        }

        if ($scope->itemId !== null) {
            $query->where('iv.inventory_item_id', $scope->itemId);
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('ii.item_name', 'like', $term)
                    ->orWhere('ii.sku', 'like', $term);
            });
        }

        if ($scope->supplierId !== null && $this->hasTable('purchase_order_items') && $this->hasTable('purchase_orders')) {
            $query->whereIn('iv.inventory_item_id', function (Builder $sub) use ($scope) {
                $sub->from('purchase_order_items as poi')
                    ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                    ->where('po.company_id', $scope->companyId)
                    ->where('po.vendor_id', $scope->supplierId)
                    ->select('poi.inventory_item_id');
            });
        }

        return $query;
    }

    protected function paginate(Builder $query, InventoryReportScope $scope, bool $grouped = false): LengthAwarePaginator
    {
        $page = $scope->page;

        if ($grouped) {
            $all = $query->get();
            $total = $all->count();
            $items = $all->forPage($page, self::PER_PAGE)->values();
        } else {
            $total = (clone $query)->count();
            $items = $query
                ->forPage($page, self::PER_PAGE)
                ->get();
        }

        return new Paginator($items, $total, self::PER_PAGE, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function totalInventoryValue(InventoryReportScope $scope): float
    {
        if (! $this->hasTable('inventory_valuations')) {
            return 0.0;
        }

        return (float) $this->valuationQuery($scope)
            ->selectRaw('COALESCE(SUM(iv.quantity_on_hand * iv.average_unit_cost), 0) as total')
            ->value('total');
    }

    public function countItemsOnHand(InventoryReportScope $scope): int
    {
        if (! $this->hasTable('inventory_valuations')) {
            return 0;
        }

        return (int) $this->valuationQuery($scope)
            ->where('iv.quantity_on_hand', '>', 0)
            ->distinct()
            ->count('iv.inventory_item_id');
    }

    public function countLowStock(InventoryReportScope $scope): int
    {
        if (! $this->hasTable('inventory_valuations')) {
            return 0;
        }

        return (int) $this->valuationQuery($scope)
            ->where('ii.reorder_level', '>', 0)
            ->whereColumn('iv.quantity_on_hand', '<=', 'ii.reorder_level')
            ->count();
    }

    public function countOutOfStock(InventoryReportScope $scope): int
    {
        if (! $this->hasTable('inventory_valuations')) {
            return 0;
        }

        return (int) $this->valuationQuery($scope)
            ->where('iv.quantity_on_hand', '<=', 0)
            ->count();
    }

    public function countSlowMoving(InventoryReportScope $scope): int
    {
        if (! $this->hasTable('inventory_movements')) {
            return 0;
        }

        return (int) $this->slowMovingQuery($scope)->count();
    }

    public function deadStockValue(InventoryReportScope $scope): float
    {
        if (! $this->hasTable('inventory_movements')) {
            return 0.0;
        }

        return (float) $this->deadStockQuery($scope)
            ->selectRaw('COALESCE(SUM(iv.quantity_on_hand * iv.average_unit_cost), 0) as total')
            ->value('total');
    }

    public function countWarehouses(InventoryReportScope $scope): int
    {
        if (! $this->hasTable('warehouses')) {
            return 0;
        }

        return (int) DB::table('warehouses')
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->when($scope->branchId, fn (Builder $q) => $q->where('branch_id', $scope->branchId))
            ->count();
    }

    public function paginateStockOnHand(InventoryReportScope $scope): LengthAwarePaginator
    {
        $query = $this->valuationQuery($scope);

        $select = [
            'ii.item_name as item',
            'ii.sku',
            'w.name as warehouse',
            DB::raw('iv.quantity_on_hand as available_qty'),
            DB::raw('0 as reserved_qty'),
            DB::raw('iv.quantity_on_hand as on_hand_qty'),
            'iv.average_unit_cost as unit_cost',
            DB::raw('(iv.quantity_on_hand * iv.average_unit_cost) as inventory_value'),
        ];

        if ($this->hasTable('inventory_categories')) {
            $select[] = DB::raw('COALESCE(ic.name, ?) as category');
            $query->select($select)->addBinding(__('Uncategorized'), 'select');
        } else {
            $select[] = DB::raw('? as category');
            $query->select($select)->addBinding(__('Uncategorized'), 'select');
        }

        $query->orderBy('ii.item_name')->orderBy('w.name');

        return $this->paginate($query, $scope);
    }

    public function paginateLowStock(InventoryReportScope $scope): LengthAwarePaginator
    {
        $periodDays = max(1, (int) now()->parse($scope->fromDate)->diffInDays(now()->parse($scope->toDate)) + 1);

        $consumptionSub = DB::table('inventory_movements as im')
            ->selectRaw('im.inventory_item_id, im.warehouse_id, ABS(SUM(im.quantity)) / ? as avg_daily', [$periodDays])
            ->where('im.company_id', $scope->companyId)
            ->whereIn('im.movement_type', [
                InventoryMovementType::Issue->value,
                InventoryMovementType::ProductionConsumption->value,
            ])
            ->whereDate('im.movement_date', '>=', $scope->fromDate)
            ->whereDate('im.movement_date', '<=', $scope->toDate)
            ->groupBy('im.inventory_item_id', 'im.warehouse_id');

        $shortfallExpr = $this->isSqlite()
            ? 'MAX(ii.reorder_level - iv.quantity_on_hand, 0)'
            : 'GREATEST(ii.reorder_level - iv.quantity_on_hand, 0)';

        $daysRemainingExpr = $this->isSqlite()
            ? 'CASE WHEN cons.avg_daily > 0 THEN CAST(iv.quantity_on_hand / cons.avg_daily AS INTEGER) ELSE NULL END'
            : 'CASE WHEN cons.avg_daily > 0 THEN ROUND(iv.quantity_on_hand / cons.avg_daily, 0) ELSE NULL END';

        $query = $this->valuationQuery($scope)
            ->leftJoinSub($consumptionSub, 'cons', function ($join) {
                $join->on('cons.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('cons.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->where('ii.reorder_level', '>', 0)
            ->whereColumn('iv.quantity_on_hand', '<=', 'ii.reorder_level')
            ->select([
                'ii.item_name as item',
                'ii.reorder_level as min_level',
                DB::raw('iv.quantity_on_hand as current_qty'),
                DB::raw("{$shortfallExpr} as shortfall"),
                DB::raw("{$daysRemainingExpr} as days_remaining"),
            ])
            ->orderByDesc('shortfall')
            ->orderBy('ii.item_name');

        return $this->paginate($query, $scope);
    }

    public function paginateOutOfStock(InventoryReportScope $scope): LengthAwarePaginator
    {
        $lastMovementSub = $this->lastMovementSubquery($scope, null);
        $lastPurchaseSub = $this->lastMovementSubquery($scope, InventoryMovementType::Receipt);

        $query = $this->valuationQuery($scope)
            ->leftJoinSub($lastMovementSub, 'lm', function ($join) {
                $join->on('lm.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('lm.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->leftJoinSub($lastPurchaseSub, 'lp', function ($join) {
                $join->on('lp.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('lp.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->where('iv.quantity_on_hand', '<=', 0)
            ->select([
                'ii.item_name as item',
                'w.name as warehouse',
                DB::raw('lm.last_date as last_movement'),
                DB::raw('lp.last_date as last_purchase'),
            ])
            ->orderBy('ii.item_name')
            ->orderBy('w.name');

        return $this->paginate($query, $scope);
    }

    protected function slowMovingQuery(InventoryReportScope $scope): Builder
    {
        $lastActivitySub = $this->lastActivitySubquery($scope);

        return $this->valuationQuery($scope)
            ->leftJoinSub($lastActivitySub, 'act', function ($join) {
                $join->on('act.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('act.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->where('iv.quantity_on_hand', '>', 0)
            ->where(function (Builder $query) use ($scope) {
                $query->whereNull('act.last_activity');

                if ($this->isSqlite()) {
                    $query->orWhereRaw('CAST(julianday(?) - julianday(act.last_activity) AS INTEGER) >= ?', [$scope->toDate, self::SLOW_MOVING_DAYS]);
                } else {
                    $query->orWhereRaw('DATEDIFF(?, act.last_activity) >= ?', [$scope->toDate, self::SLOW_MOVING_DAYS]);
                }
            });
    }

    public function paginateSlowMoving(InventoryReportScope $scope): LengthAwarePaginator
    {
        $lastSaleSub = $this->lastMovementSubquery($scope, InventoryMovementType::Issue);
        $lastConsumptionSub = $this->lastMovementSubquery($scope, InventoryMovementType::ProductionConsumption);

        $query = $this->slowMovingQuery($scope)
            ->leftJoinSub($lastSaleSub, 'ls', function ($join) {
                $join->on('ls.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('ls.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->leftJoinSub($lastConsumptionSub, 'lc', function ($join) {
                $join->on('lc.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('lc.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->select([
                'ii.item_name as item',
                DB::raw('ls.last_date as last_sale'),
                DB::raw('lc.last_date as last_consumption'),
                DB::raw($this->daysIdleSelectExpression()),
                DB::raw('(iv.quantity_on_hand * iv.average_unit_cost) as value_locked'),
            ])
            ->leftJoinSub($this->lastMovementSubquery($scope, InventoryMovementType::Receipt), 'lr', function ($join) {
                $join->on('lr.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('lr.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->addBinding($scope->toDate, 'select')
            ->addBinding($scope->toDate, 'select')
            ->orderByDesc('days_idle')
            ->orderBy('ii.item_name');

        return $this->paginate($query, $scope);
    }

    protected function deadStockQuery(InventoryReportScope $scope): Builder
    {
        $lastAnyMovementSub = DB::table('inventory_movements as im')
            ->selectRaw('im.inventory_item_id, im.warehouse_id, MAX(im.movement_date) as last_movement')
            ->where('im.company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('im.branch_id', $scope->branchId))
            ->groupBy('im.inventory_item_id', 'im.warehouse_id');

        $query = $this->valuationQuery($scope)
            ->joinSub($lastAnyMovementSub, 'mv', function ($join) {
                $join->on('mv.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('mv.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->where('iv.quantity_on_hand', '>', 0);

        if ($this->isSqlite()) {
            $query->whereRaw('CAST(julianday(?) - julianday(mv.last_movement) AS INTEGER) >= ?', [$scope->toDate, self::DEAD_STOCK_DAYS]);
        } else {
            $query->whereRaw('DATEDIFF(?, mv.last_movement) >= ?', [$scope->toDate, self::DEAD_STOCK_DAYS]);
        }

        return $query;
    }

    public function paginateDeadStock(InventoryReportScope $scope): LengthAwarePaginator
    {
        $query = $this->deadStockQuery($scope)
            ->select([
                'ii.item_name as item',
                DB::raw($this->daysSinceColumn('mv.last_movement').' as days_without_movement'),
                DB::raw('iv.quantity_on_hand as qty'),
                DB::raw('(iv.quantity_on_hand * iv.average_unit_cost) as value'),
            ])
            ->addBinding($scope->toDate, 'select')
            ->orderByDesc('days_without_movement')
            ->orderBy('ii.item_name');

        return $this->paginate($query, $scope);
    }

    /**
     * @return array<string, array{qty: float, value: float, items: int}>
     */
    public function stockAgingBuckets(InventoryReportScope $scope): array
    {
        if (! $this->hasTable('inventory_movements')) {
            return $this->emptyAgingBuckets();
        }

        $lastReceiptSub = $this->lastMovementSubquery($scope, InventoryMovementType::Receipt);

        $rows = $this->valuationQuery($scope)
            ->joinSub($lastReceiptSub, 'lr', function ($join) {
                $join->on('lr.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('lr.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->where('iv.quantity_on_hand', '>', 0)
            ->select([
                DB::raw($this->daysSinceColumn('lr.last_date').' as age_days'),
                DB::raw('iv.quantity_on_hand as qty'),
                DB::raw('(iv.quantity_on_hand * iv.average_unit_cost) as value'),
            ])
            ->addBinding($scope->toDate, 'select')
            ->get();

        $buckets = $this->emptyAgingBuckets();

        foreach ($rows as $row) {
            $key = $this->agingBucketKey((int) $row->age_days);
            $buckets[$key]['qty'] += (float) $row->qty;
            $buckets[$key]['value'] += (float) $row->value;
            $buckets[$key]['items']++;
        }

        return $buckets;
    }

    public function paginateStockAging(InventoryReportScope $scope): LengthAwarePaginator
    {
        $lastReceiptSub = $this->lastMovementSubquery($scope, InventoryMovementType::Receipt);

        $query = $this->valuationQuery($scope)
            ->joinSub($lastReceiptSub, 'lr', function ($join) {
                $join->on('lr.inventory_item_id', '=', 'iv.inventory_item_id')
                    ->on('lr.warehouse_id', '=', 'iv.warehouse_id');
            })
            ->where('iv.quantity_on_hand', '>', 0)
            ->select([
                'ii.item_name as item',
                'w.name as warehouse',
                DB::raw('lr.last_date as last_receipt'),
                DB::raw($this->daysSinceColumn('lr.last_date').' as age_days'),
                DB::raw('iv.quantity_on_hand as qty'),
                DB::raw('(iv.quantity_on_hand * iv.average_unit_cost) as value'),
            ])
            ->addBinding($scope->toDate, 'select')
            ->orderByDesc('age_days')
            ->orderBy('ii.item_name');

        return $this->paginate($query, $scope);
    }

    public function paginateWarehouseSummary(InventoryReportScope $scope): LengthAwarePaginator
    {
        $query = DB::table('warehouses as w')
            ->leftJoin('inventory_valuations as iv', function ($join) use ($scope) {
                $join->on('iv.warehouse_id', '=', 'w.id')
                    ->where('iv.company_id', '=', $scope->companyId);

                if ($scope->branchId !== null) {
                    $join->where('iv.branch_id', '=', $scope->branchId);
                }
            })
            ->leftJoin('inventory_items as ii', function ($join) use ($scope) {
                $join->on('ii.id', '=', 'iv.inventory_item_id');

                if ($scope->categoryId !== null) {
                    $join->where('ii.inventory_category_id', '=', $scope->categoryId);
                }

                if ($scope->itemId !== null) {
                    $join->where('ii.id', '=', $scope->itemId);
                }
            })
            ->where('w.company_id', $scope->companyId)
            ->where('w.is_active', true)
            ->when($scope->branchId, fn (Builder $q) => $q->where('w.branch_id', $scope->branchId))
            ->when($scope->warehouseId, fn (Builder $q) => $q->where('w.id', $scope->warehouseId))
            ->groupBy('w.id', 'w.name')
            ->select([
                'w.name as warehouse',
                DB::raw('COUNT(DISTINCT CASE WHEN iv.quantity_on_hand > 0 THEN iv.inventory_item_id END) as items'),
                DB::raw('COALESCE(SUM(iv.quantity_on_hand), 0) as qty'),
                DB::raw('COALESCE(SUM(iv.quantity_on_hand * iv.average_unit_cost), 0) as value'),
            ])
            ->orderBy('w.name');

        return $this->paginate($query, $scope, grouped: true);
    }

    /**
     * @return Collection<int, object>
     */
    public function allStockOnHand(InventoryReportScope $scope): Collection
    {
        return collect($this->paginateStockOnHand($scope->withPage(1))->items());
    }

    public function withPage(InventoryReportScope $scope, int $page): InventoryReportScope
    {
        return new InventoryReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            warehouseId: $scope->warehouseId,
            categoryId: $scope->categoryId,
            supplierId: $scope->supplierId,
            itemId: $scope->itemId,
            search: $scope->search,
            tab: $scope->tab,
            page: $page,
        );
    }

    protected function lastMovementSubquery(InventoryReportScope $scope, ?InventoryMovementType $type): Builder
    {
        $query = DB::table('inventory_movements as im')
            ->selectRaw('im.inventory_item_id, im.warehouse_id, MAX(im.movement_date) as last_date')
            ->where('im.company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('im.branch_id', $scope->branchId))
            ->when($type !== null, fn (Builder $q) => $q->where('im.movement_type', $type->value))
            ->groupBy('im.inventory_item_id', 'im.warehouse_id');

        return $query;
    }

    protected function lastActivitySubquery(InventoryReportScope $scope): Builder
    {
        return DB::table('inventory_movements as im')
            ->selectRaw('im.inventory_item_id, im.warehouse_id, MAX(im.movement_date) as last_activity')
            ->where('im.company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('im.branch_id', $scope->branchId))
            ->whereIn('im.movement_type', [
                InventoryMovementType::Issue->value,
                InventoryMovementType::ProductionConsumption->value,
            ])
            ->groupBy('im.inventory_item_id', 'im.warehouse_id');
    }

    protected function agingBucketKey(int $days): string
    {
        return match (true) {
            $days <= 30 => '0_30',
            $days <= 60 => '31_60',
            $days <= 90 => '61_90',
            default => '90_plus',
        };
    }

    /**
     * @return array<string, array{qty: float, value: float, items: int}>
     */
    protected function emptyAgingBuckets(): array
    {
        return [
            '0_30' => ['qty' => 0.0, 'value' => 0.0, 'items' => 0],
            '31_60' => ['qty' => 0.0, 'value' => 0.0, 'items' => 0],
            '61_90' => ['qty' => 0.0, 'value' => 0.0, 'items' => 0],
            '90_plus' => ['qty' => 0.0, 'value' => 0.0, 'items' => 0],
        ];
    }

    protected function isSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }

    protected function daysSinceColumn(string $dateColumn): string
    {
        if ($this->isSqlite()) {
            return "CAST(julianday(?) - julianday({$dateColumn}) AS INTEGER)";
        }

        return "DATEDIFF(?, {$dateColumn})";
    }

    protected function daysIdleSelectExpression(): string
    {
        if ($this->isSqlite()) {
            return 'CASE WHEN act.last_activity IS NULL THEN CAST(julianday(?) - julianday(lr.last_date) AS INTEGER) ELSE CAST(julianday(?) - julianday(act.last_activity) AS INTEGER) END as days_idle';
        }

        return 'CASE WHEN act.last_activity IS NULL THEN DATEDIFF(?, lr.last_date) ELSE DATEDIFF(?, act.last_activity) END as days_idle';
    }
}
