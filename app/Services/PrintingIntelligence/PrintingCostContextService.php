<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryValuation;
use App\Models\Inventory\InventoryVelocitySnapshot;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\DeadStockDetectionService;
use App\Services\Inventory\InventoryVelocityService;
use App\Support\Inventory\InventoryCostingService;
use App\Support\InventoryStockService;

class PrintingCostContextService
{
    public function __construct(
        protected InventoryVelocityService $velocityService,
        protected DeadStockDetectionService $deadStockService,
    ) {}

    /**
     * @return array{unit_cost: float, average_cost: float, standard_cost: float, source: string}
     */
    public function getPaperCost(int $inventoryItemId, ?int $warehouseId = null): array
    {
        return $this->getMaterialCost($inventoryItemId, $warehouseId);
    }

    /**
     * @return array{unit_cost: float, average_cost: float, standard_cost: float, source: string}
     */
    public function getMaterialCost(int $inventoryItemId, ?int $warehouseId = null): array
    {
        return $this->getInventoryCost($inventoryItemId, $warehouseId);
    }

    /**
     * @return array{unit_cost: float, average_cost: float, standard_cost: float, source: string}
     */
    public function getInventoryCost(int $inventoryItemId, ?int $warehouseId = null): array
    {
        $item = InventoryItem::query()->find($inventoryItemId);

        if ($item === null) {
            return [
                'unit_cost' => 0.0,
                'average_cost' => 0.0,
                'standard_cost' => 0.0,
                'source' => 'missing_item',
            ];
        }

        $warehouseId ??= $this->defaultWarehouseId($item);

        return [
            'unit_cost' => $this->getCurrentUnitCost($inventoryItemId, $warehouseId),
            'average_cost' => $this->getCurrentAverageCost($inventoryItemId, $warehouseId),
            'standard_cost' => (float) $item->standard_cost,
            'source' => 'inventory_costing',
        ];
    }

    public function getCurrentUnitCost(int $inventoryItemId, int $warehouseId, float $quantity = 1): float
    {
        $item = InventoryItem::query()->find($inventoryItemId);

        if ($item === null) {
            return 0.0;
        }

        return InventoryCostingService::resolveIssueUnitCost(
            (int) $item->company_id,
            (int) $item->branch_id,
            $inventoryItemId,
            $warehouseId,
            max($quantity, 0.001),
        );
    }

    public function getCurrentAverageCost(int $inventoryItemId, int $warehouseId): float
    {
        $valuation = InventoryValuation::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($valuation !== null && (float) $valuation->average_unit_cost > 0) {
            return (float) $valuation->average_unit_cost;
        }

        return (float) (InventoryItem::query()->find($inventoryItemId)?->standard_cost ?? 0);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getVelocityData(int $inventoryItemId, ?int $warehouseId = null, int $windowDays = 30): ?array
    {
        $item = InventoryItem::query()->find($inventoryItemId);

        if ($item === null) {
            return null;
        }

        $snapshot = InventoryVelocitySnapshot::query()
            ->where('company_id', $item->company_id)
            ->where('inventory_item_id', $inventoryItemId)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->where('movement_window_days', $windowDays)
            ->where('period_end', today()->toDateString())
            ->latest('generated_at')
            ->first();

        if ($snapshot === null) {
            return null;
        }

        return [
            'average_daily_consumption' => (float) $snapshot->average_daily_consumption,
            'days_to_depletion' => $snapshot->days_to_depletion !== null ? (float) $snapshot->days_to_depletion : null,
            'velocity_class' => $snapshot->velocity_class?->value,
            'risk_level' => $snapshot->risk_level?->value,
            'closing_balance' => (float) $snapshot->closing_balance,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDeadStockData(int $companyId, ?int $branchId = null): array
    {
        return $this->deadStockService
            ->detect($companyId, ['branch_id' => $branchId])
            ->take(50)
            ->values()
            ->all();
    }

    protected function defaultWarehouseId(InventoryItem $item): int
    {
        return (int) (Warehouse::query()
            ->where('company_id', $item->company_id)
            ->where('branch_id', $item->branch_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id') ?? 0);
    }

    public function stockBalance(int $inventoryItemId, int $warehouseId): float
    {
        return InventoryStockService::balance($inventoryItemId, $warehouseId);
    }
}
