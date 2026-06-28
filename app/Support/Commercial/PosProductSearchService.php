<?php

namespace App\Support\Commercial;

use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PosProductSearchService
{
    public function __construct(
        protected CommercialPriceBookService $priceBooks,
    ) {}

    /**
     * @return Collection<int, array{id: int, name: string, sku: string|null, unit_price: float}>
     */
    public function search(string $query, ?int $customerId = null, int $limit = 15): Collection
    {
        $term = trim($query);

        if ($term === '') {
            return collect();
        }

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return InventoryItem::query()
            ->forTenant()
            ->where('is_active', true)
            ->where(function (Builder $builder) use ($term): void {
                $like = '%'.$term.'%';
                $builder
                    ->where('item_name', 'like', $like)
                    ->orWhere('sku', 'like', $like);
            })
            ->orderBy('item_name')
            ->limit($limit)
            ->get(['id', 'item_name', 'sku', 'standard_cost', 'company_id'])
            ->map(fn (InventoryItem $item) => $this->toSearchResult($item, $customerId, $companyId, $branchId));
    }

    /**
     * Exact barcode / SKU match for scanner workflows.
     *
     * @return array{id: int, name: string, sku: string|null, unit_price: float}|null
     */
    public function findByBarcode(string $barcode, ?int $customerId = null): ?array
    {
        $code = trim($barcode);

        if ($code === '') {
            return null;
        }

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $item = InventoryItem::query()
            ->forTenant()
            ->where('is_active', true)
            ->where('sku', $code)
            ->first(['id', 'item_name', 'sku', 'standard_cost', 'company_id']);

        return $item ? $this->toSearchResult($item, $customerId, $companyId, $branchId) : null;
    }

    /**
     * @return array{id: int, name: string, sku: string|null, unit_price: float}
     */
    protected function toSearchResult(InventoryItem $item, ?int $customerId, int $companyId, ?int $branchId): array
    {
        $unitPrice = $this->priceBooks->resolveInventoryFallbackPrice($item, $customerId, $companyId, $branchId);

        return [
            'id' => (int) $item->id,
            'name' => (string) $item->item_name,
            'sku' => $item->sku,
            'unit_price' => $unitPrice,
        ];
    }
}
