<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Inventory\InventoryItem;

class MaterialCostContextService
{
    public function __construct(
        protected PrintingCostContextService $costContext,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function context(int $inventoryItemId, ?int $warehouseId = null): array
    {
        $item = InventoryItem::query()->with('category')->find($inventoryItemId);

        if ($item === null) {
            return ['found' => false];
        }

        $warehouseId ??= $this->resolveWarehouseId($item);

        return [
            'found' => true,
            'item_id' => $item->id,
            'sku' => $item->sku,
            'name' => $item->item_name,
            'category' => $item->category?->name,
            'material_key' => $this->resolveMaterialKey($item->item_name, $item->category?->name),
            'current_cost' => $this->currentCost($inventoryItemId, $warehouseId),
            'average_cost' => $this->averageCost($inventoryItemId, $warehouseId),
            'velocity' => $this->velocity($inventoryItemId, $warehouseId),
            'stock_availability' => $this->stockAvailability($inventoryItemId, $warehouseId),
            'risk_level' => $this->riskLevel($inventoryItemId, $warehouseId),
        ];
    }

    public function currentCost(int $inventoryItemId, ?int $warehouseId = null): float
    {
        $item = InventoryItem::query()->find($inventoryItemId);

        if ($item === null) {
            return 0.0;
        }

        $warehouseId ??= $this->resolveWarehouseId($item);

        return $this->costContext->getCurrentUnitCost($inventoryItemId, $warehouseId);
    }

    public function averageCost(int $inventoryItemId, ?int $warehouseId = null): float
    {
        $item = InventoryItem::query()->find($inventoryItemId);

        if ($item === null) {
            return 0.0;
        }

        $warehouseId ??= $this->resolveWarehouseId($item);

        return $this->costContext->getCurrentAverageCost($inventoryItemId, $warehouseId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function velocity(int $inventoryItemId, ?int $warehouseId = null): ?array
    {
        return $this->costContext->getVelocityData($inventoryItemId, $warehouseId);
    }

    public function stockAvailability(int $inventoryItemId, ?int $warehouseId = null): float
    {
        $item = InventoryItem::query()->find($inventoryItemId);

        if ($item === null) {
            return 0.0;
        }

        $warehouseId ??= $this->resolveWarehouseId($item);

        return $this->costContext->stockBalance($inventoryItemId, $warehouseId);
    }

    public function riskLevel(int $inventoryItemId, ?int $warehouseId = null): ?string
    {
        return $this->costContext->getVelocityData($inventoryItemId, $warehouseId)['risk_level'] ?? null;
    }

    protected function resolveWarehouseId(InventoryItem $item): int
    {
        return (int) (\App\Models\Inventory\Warehouse::query()
            ->where('company_id', $item->company_id)
            ->where('branch_id', $item->branch_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id') ?? 0);
    }

    protected function resolveMaterialKey(?string $itemName, ?string $categoryName): ?string
    {
        $haystack = strtolower(trim(($itemName ?? '').' '.($categoryName ?? '')));

        foreach (config('printing_intelligence.material_aliases', []) as $key => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($haystack, strtolower($alias))) {
                    return $key;
                }
            }
        }

        return null;
    }
}
