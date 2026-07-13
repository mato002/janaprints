<?php

namespace App\Support\Inventory;

use App\Enums\NormalBalance;
use App\Models\Accounting\GlAccount;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryCostLayer;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryValuation;
use App\Models\Inventory\InventoryValuationSnapshot;
use App\Models\Inventory\Warehouse;
use App\Models\Branch;
use App\Support\Accounting\LedgerSignedBalance;
use App\Support\Accounting\Reports\PostedJournalQuery;
use App\Support\InventoryStockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryValuationService
{
    /**
     * @return array{fifo_value: float, average_cost_value: float, quantity: float}
     */
    public static function itemWarehouseValue(int $itemId, int $warehouseId): array
    {
        $qty = InventoryStockService::balanceUncached($itemId, $warehouseId);
        $fifo = InventoryCostingService::fifoValue($itemId, $warehouseId);

        $valuation = InventoryValuation::query()
            ->where('inventory_item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $avgUnit = (float) ($valuation?->average_unit_cost ?? 0);
        if ($avgUnit <= 0) {
            $item = InventoryItem::query()->find($itemId);
            $avgUnit = (float) ($item?->standard_cost ?? 0);
        }

        return [
            'quantity' => $qty,
            'fifo_value' => round($fifo, 2),
            'average_cost_value' => round($qty * $avgUnit, 2),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function byItem(int $companyId, ?int $branchId = null): Collection
    {
        $query = InventoryItem::query()->with('category')->where('company_id', $companyId);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get()->map(function (InventoryItem $item) use ($branchId) {
            $warehouses = Warehouse::query()
                ->where('company_id', $item->company_id)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('is_active', true)
                ->get();

            $fifo = 0.0;
            $avg = 0.0;
            $qty = 0.0;

            foreach ($warehouses as $warehouse) {
                $values = self::itemWarehouseValue($item->id, $warehouse->id);
                $fifo += $values['fifo_value'];
                $avg += $values['average_cost_value'];
                $qty += $values['quantity'];
            }

            return [
                'item' => $item,
                'quantity' => $qty,
                'fifo_value' => $fifo,
                'average_cost_value' => $avg,
            ];
        });
    }

    public static function snapshot(
        int $companyId,
        ?int $branchId,
        string $valuationDate,
        string $scope = 'branch',
    ): void {
        DB::transaction(function () use ($companyId, $branchId, $valuationDate, $scope) {
            $rows = self::byItem($companyId, $branchId);

            foreach ($rows as $row) {
                InventoryValuationSnapshot::query()->create([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'valuation_date' => $valuationDate,
                    'snapshot_scope' => $scope,
                    'inventory_item_id' => $row['item']->id,
                    'quantity' => $row['quantity'],
                    'fifo_value' => $row['fifo_value'],
                    'average_cost_value' => $row['average_cost_value'],
                ]);
            }
        });
    }

    /**
     * @return array<string, float|int>
     */
    public static function dashboardTotals(int $companyId, ?int $branchId = null): array
    {
        $rows = self::byItem($companyId, $branchId);

        $fifoTotal = $rows->sum('fifo_value');
        $avgTotal = $rows->sum('average_cost_value');

        $topItems = $rows->sortByDesc('fifo_value')->take(5)->values();

        $deadStock = $rows->filter(fn ($r) => $r['quantity'] <= 0 && $r['fifo_value'] > 0);

        return [
            'fifo_total' => round($fifoTotal, 2),
            'average_total' => round($avgTotal, 2),
            'top_items' => $topItems,
            'dead_stock_value' => round($deadStock->sum('fifo_value'), 2),
            'item_count' => $rows->count(),
        ];
    }

    public static function layersRemainingValue(int $companyId, ?int $branchId = null): float
    {
        $query = InventoryCostLayer::query()
            ->where('company_id', $companyId)
            ->where('quantity_remaining', '>', 0);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return (float) $query
            ->selectRaw('SUM(quantity_remaining * unit_cost) as total')
            ->value('total');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function byWarehouse(int $companyId, ?int $branchId = null): Collection
    {
        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $items = InventoryItem::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        return $warehouses->map(function (Warehouse $warehouse) use ($items) {
            $fifo = 0.0;
            $avg = 0.0;
            $qty = 0.0;

            foreach ($items as $item) {
                $values = self::itemWarehouseValue($item->id, $warehouse->id);
                $fifo += $values['fifo_value'];
                $avg += $values['average_cost_value'];
                $qty += $values['quantity'];
            }

            return [
                'warehouse' => $warehouse,
                'quantity' => $qty,
                'fifo_value' => round($fifo, 2),
                'average_cost_value' => round($avg, 2),
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function byCategory(int $companyId, ?int $branchId = null): Collection
    {
        $rows = self::byItem($companyId, $branchId);

        return $rows
            ->groupBy(fn ($r) => $r['item']->inventory_category_id)
            ->map(function ($group, $categoryId) {
                $category = $group->first()['item']->category
                    ?? InventoryCategory::query()->find($categoryId);

                return [
                    'category' => $category,
                    'category_name' => $category?->name ?? __('Uncategorised'),
                    'quantity' => $group->sum('quantity'),
                    'fifo_value' => round($group->sum('fifo_value'), 2),
                    'average_cost_value' => round($group->sum('average_cost_value'), 2),
                    'item_count' => $group->count(),
                ];
            })
            ->sortByDesc('fifo_value')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function byBranch(int $companyId): Collection
    {
        $branches = Branch::query()->where('company_id', $companyId)->orderBy('name')->get();

        return $branches->map(function (Branch $branch) use ($companyId) {
            $totals = self::dashboardTotals($companyId, $branch->id);

            return [
                'branch' => $branch,
                'fifo_value' => $totals['fifo_total'],
                'average_cost_value' => $totals['average_total'],
                'item_count' => $totals['item_count'],
            ];
        });
    }

    /**
     * @return array{gl_balance: float, fifo_layers: float, variance: float, account_code: string}
     */
    public static function inventoryGlReconciliation(int $companyId, ?float $fifoTotal = null): array
    {
        $code = config('posting_account_keys.raw_materials.default_code', '1410');
        $account = GlAccount::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->first();

        $glBalance = 0.0;

        if ($account) {
            $agg = PostedJournalQuery::aggregateByAccount([
                'company_id' => $companyId,
            ])->where('gl_account_id', $account->id)->first();

            if ($agg) {
                $glBalance = LedgerSignedBalance::balanceSheetAmount(
                    (float) $agg->total_debit,
                    (float) $agg->total_credit,
                    $account->normal_balance instanceof NormalBalance
                        ? $account->normal_balance
                        : NormalBalance::from($account->normal_balance),
                );
            }
        }

        $fifoTotal ??= self::layersRemainingValue($companyId);

        return [
            'account_code' => $code,
            'gl_balance' => round($glBalance, 2),
            'fifo_layers' => round($fifoTotal, 2),
            'variance' => round($glBalance - $fifoTotal, 2),
        ];
    }
}
