<?php

namespace App\Support\Inventory;

use App\Enums\ReplenishmentRecommendation;
use App\Enums\ReorderAlertStatus;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryItemWarehouseReorderSetting;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\Warehouse;
use App\Support\InventoryStockService;
use App\Support\Platform\SystemSettingsService;
use Illuminate\Support\Collection;

class WarehouseReorderIntelligenceService
{
    /**
     * @return array{min_level: float, max_level: ?float, reorder_quantity: float, safety_stock: float}
     */
    public function resolveConfig(InventoryItem $item, Warehouse $warehouse): array
    {
        $setting = InventoryItemWarehouseReorderSetting::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('inventory_item_id', $item->id)
            ->where('is_active', true)
            ->first();

        if ($setting !== null) {
            return [
                'min_level' => (float) $setting->min_level,
                'max_level' => $setting->max_level !== null ? (float) $setting->max_level : null,
                'reorder_quantity' => (float) $setting->reorder_quantity,
                'safety_stock' => (float) $setting->safety_stock,
            ];
        }

        return [
            'min_level' => (float) $item->reorder_level,
            'max_level' => null,
            'reorder_quantity' => (float) $item->reorder_quantity,
            'safety_stock' => 0,
        ];
    }

    public function syncAlertForWarehouse(InventoryItem $item, Warehouse $warehouse): void
    {
        if (! $this->alertsEnabled($item->company_id, $item->branch_id)) {
            return;
        }

        $config = $this->resolveConfig($item, $warehouse);
        $balance = InventoryStockService::balance($item->id, $warehouse->id);
        $minLevel = $config['min_level'];

        if ($minLevel <= 0) {
            return;
        }

        $existing = InventoryReorderAlert::query()
            ->where('company_id', $item->company_id)
            ->where('branch_id', $item->branch_id)
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('alert_type', config('inventory_intelligence.reorder_alert_type', 'reorder_level'))
            ->first();

        if ($balance <= $minLevel) {
            $recommendation = $this->recommendReplenishment($item, $warehouse, $balance, $config);

            InventoryReorderAlert::query()->updateOrCreate(
                [
                    'company_id' => $item->company_id,
                    'branch_id' => $item->branch_id,
                    'inventory_item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'alert_type' => config('inventory_intelligence.reorder_alert_type', 'reorder_level'),
                ],
                [
                    'current_quantity' => $balance,
                    'reorder_level' => $minLevel,
                    'max_level' => $config['max_level'],
                    'reorder_quantity' => $config['reorder_quantity'],
                    'safety_stock' => $config['safety_stock'],
                    'replenishment_action' => $recommendation['action']->value,
                    'source_warehouse_id' => $recommendation['source_warehouse_id'],
                    'recommended_quantity' => $recommendation['quantity'],
                    'is_resolved' => false,
                    'status' => $existing?->status === ReorderAlertStatus::Acknowledged
                        ? ReorderAlertStatus::Acknowledged
                        : ReorderAlertStatus::Open,
                    'alerted_at' => $existing?->alerted_at ?? now(),
                ],
            );
        } elseif ($existing !== null && $existing->status->isActionable()) {
            $existing->update([
                'status' => ReorderAlertStatus::Resolved,
                'is_resolved' => true,
                'resolved_at' => now(),
            ]);
        }
    }

    /**
     * @param  array{min_level: float, max_level: ?float, reorder_quantity: float, safety_stock: float}  $config
     * @return array{action: ReplenishmentRecommendation, source_warehouse_id: ?int, quantity: float}
     */
    public function recommendReplenishment(
        InventoryItem $item,
        Warehouse $destinationWarehouse,
        float $currentBalance,
        array $config,
    ): array {
        $shortage = max(0, $config['min_level'] - $currentBalance);
        $targetQty = $config['reorder_quantity'] > 0
            ? (float) $config['reorder_quantity']
            : max($shortage, 1);

        $sources = Warehouse::query()
            ->where('company_id', $item->company_id)
            ->where('branch_id', $item->branch_id)
            ->where('is_active', true)
            ->whereKeyNot($destinationWarehouse->id)
            ->orderBy('name')
            ->get();

        foreach ($sources as $source) {
            $sourceBalance = InventoryStockService::balance($item->id, $source->id);
            $sourceConfig = $this->resolveConfig($item, $source);
            $reserve = max($sourceConfig['safety_stock'], $sourceConfig['min_level']);
            $available = $sourceBalance - $reserve;

            if ($available >= $targetQty) {
                return [
                    'action' => ReplenishmentRecommendation::Transfer,
                    'source_warehouse_id' => $source->id,
                    'quantity' => round(min($available, $targetQty), 3),
                ];
            }
        }

        return [
            'action' => ReplenishmentRecommendation::Purchase,
            'source_warehouse_id' => null,
            'quantity' => round($targetQty, 3),
        ];
    }

    /**
     * @return Collection<int, InventoryReorderAlert>
     */
    public function criticalShortages(int $companyId, int $branchId, int $limit = 10): Collection
    {
        return InventoryReorderAlert::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('status', [ReorderAlertStatus::Open, ReorderAlertStatus::Acknowledged])
            ->where('current_quantity', '<=', 0)
            ->with(['inventoryItem', 'warehouse'])
            ->latest('alerted_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return list<array{warehouse: string, open_alerts: int, critical: int, transfer_suggested: int}>
     */
    public function warehouseStockHealth(int $companyId, int $branchId): array
    {
        $alerts = InventoryReorderAlert::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('status', [ReorderAlertStatus::Open, ReorderAlertStatus::Acknowledged])
            ->with('warehouse:id,name')
            ->get();

        return $alerts
            ->groupBy('warehouse_id')
            ->map(function (Collection $rows) {
                $warehouse = $rows->first()?->warehouse;

                return [
                    'warehouse' => $warehouse?->name ?? __('Unknown'),
                    'open_alerts' => $rows->count(),
                    'critical' => $rows->where('current_quantity', '<=', 0)->count(),
                    'transfer_suggested' => $rows->where('replenishment_action', ReplenishmentRecommendation::Transfer->value)->count(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, InventoryReorderAlert>
     */
    public function transferRecommendations(int $companyId, int $branchId, int $limit = 10): Collection
    {
        return InventoryReorderAlert::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('status', [ReorderAlertStatus::Open, ReorderAlertStatus::Acknowledged])
            ->where('replenishment_action', ReplenishmentRecommendation::Transfer->value)
            ->with(['inventoryItem', 'warehouse', 'sourceWarehouse'])
            ->latest('alerted_at')
            ->limit($limit)
            ->get();
    }

    protected function alertsEnabled(int $companyId, int $branchId): bool
    {
        return (bool) app(SystemSettingsService::class)->get(
            'inventory_reorder_alert_enabled',
            true,
            $companyId,
            $branchId,
        );
    }
}
