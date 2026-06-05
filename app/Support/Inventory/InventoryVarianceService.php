<?php

namespace App\Support\Inventory;

use App\Models\Inventory\StockCountItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InventoryVarianceService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<StockCountItem>
     */
    public static function query(int $companyId, ?int $branchId = null, array $filters = []): Builder
    {
        $query = StockCountItem::query()
            ->join('stock_counts', 'stock_counts.id', '=', 'stock_count_items.stock_count_id')
            ->where('stock_counts.company_id', $companyId)
            ->where('stock_count_items.variance_quantity', '!=', 0)
            ->select('stock_count_items.*')
            ->with([
                'inventoryItem.category',
                'stockCount.warehouse',
                'stockCount.approver',
                'stockCount.poster',
            ]);

        if ($branchId) {
            $query->where('stock_counts.branch_id', $branchId);
        }

        if (! empty($filters['warehouse_id'])) {
            $query->where('stock_counts.warehouse_id', $filters['warehouse_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('stock_counts.status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('stock_counts.count_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('stock_counts.count_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['category_id'])) {
            $query->whereHas('inventoryItem', fn (Builder $q) => $q->where('inventory_category_id', $filters['category_id']));
        }

        if (! empty($filters['item_id'])) {
            $query->where('stock_count_items.inventory_item_id', $filters['item_id']);
        }

        if (! empty($filters['variance_type'])) {
            match ($filters['variance_type']) {
                'positive' => $query->where('stock_count_items.variance_quantity', '>', 0),
                'negative' => $query->where('stock_count_items.variance_quantity', '<', 0),
                'zero' => $query->where('stock_count_items.variance_quantity', '=', 0),
                default => null,
            };
        }

        return $query->orderByDesc('stock_counts.count_date');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function exportRows(int $companyId, ?int $branchId = null, array $filters = []): Collection
    {
        return self::query($companyId, $branchId, $filters)->get();
    }
}
