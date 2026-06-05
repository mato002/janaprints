<?php

namespace App\Support\Commercial;

use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PosProductSearchService
{
    /**
     * @return Collection<int, array{id: int, name: string, sku: string|null, item_code: string|null, unit_price: float}>
     */
    public function search(string $query, int $limit = 15): Collection
    {
        $term = trim($query);

        if ($term === '') {
            return collect();
        }

        return InventoryItem::query()
            ->forTenant()
            ->where('is_active', true)
            ->where(function (Builder $builder) use ($term): void {
                $like = '%'.$term.'%';
                $builder
                    ->where('item_name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('item_code', 'like', $like);
            })
            ->orderBy('item_name')
            ->limit($limit)
            ->get(['id', 'item_name', 'sku', 'item_code', 'standard_cost'])
            ->map(fn (InventoryItem $item) => $this->toSearchResult($item));
    }

    /**
     * Exact barcode / SKU match for scanner workflows.
     *
     * @return array{id: int, name: string, sku: string|null, item_code: string|null, unit_price: float}|null
     */
    public function findByBarcode(string $barcode): ?array
    {
        $code = trim($barcode);

        if ($code === '') {
            return null;
        }

        $item = InventoryItem::query()
            ->forTenant()
            ->where('is_active', true)
            ->where(function (Builder $builder) use ($code): void {
                $builder
                    ->where('sku', $code)
                    ->orWhere('item_code', $code);
            })
            ->first(['id', 'item_name', 'sku', 'item_code', 'standard_cost']);

        return $item ? $this->toSearchResult($item) : null;
    }

    /**
     * @return array{id: int, name: string, sku: string|null, item_code: string|null, unit_price: float}
     */
    protected function toSearchResult(InventoryItem $item): array
    {
        return [
            'id' => (int) $item->id,
            'name' => (string) $item->item_name,
            'sku' => $item->sku,
            'item_code' => $item->item_code,
            'unit_price' => (float) $item->standard_cost,
        ];
    }
}
