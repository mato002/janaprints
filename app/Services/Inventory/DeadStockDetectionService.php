<?php

namespace App\Services\Inventory;

use App\Enums\DeadStockSuggestedAction;
use App\Enums\InventoryMovementType;
use App\Enums\InventoryStockRole;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\InventoryMovementAnalytics;
use App\Support\InventoryStockService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DeadStockDetectionService
{
    /**
     * @param  array{
     *     warehouse_id?: int|null,
     *     stock_role?: string|null,
     *     category_id?: int|null,
     *     branch_id?: int|null
     * }  $filters
     * @return Collection<int, array{
     *     item: InventoryItem,
     *     warehouse: Warehouse|null,
     *     balance: float,
     *     estimated_value: float,
     *     last_movement_at: Carbon|null,
     *     last_outbound_at: Carbon|null,
     *     days_inactive: int,
     *     suggested_action: DeadStockSuggestedAction,
     *     stock_role: string|null
     * }>
     */
    public function detect(int $companyId, array $filters = []): Collection
    {
        $deadStockDays = (int) config('inventory_intelligence.dead_stock_days', 60);
        $branchId = $filters['branch_id'] ?? null;

        $items = InventoryItem::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(! empty($filters['stock_role']), fn ($q) => $q->where('stock_role', $filters['stock_role']))
            ->when(! empty($filters['category_id']), fn ($q) => $q->where('inventory_category_id', (int) $filters['category_id']))
            ->where('is_active', true)
            ->get();

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(! empty($filters['warehouse_id']), fn ($q) => $q->whereKey((int) $filters['warehouse_id']))
            ->where('is_active', true)
            ->get();

        $results = collect();

        foreach ($items as $item) {
            foreach ($warehouses as $warehouse) {
                $balance = InventoryStockService::balance($item->id, $warehouse->id);

                if ($balance <= 0) {
                    continue;
                }

                $movementStats = $this->movementStats($item->id, $warehouse->id, $companyId);
                $daysInactive = $movementStats['last_outbound_at'] === null
                    ? ($movementStats['last_movement_at']?->diffInDays(today()) ?? $deadStockDays)
                    : $movementStats['last_outbound_at']->diffInDays(today());

                if ($daysInactive < $deadStockDays) {
                    continue;
                }

                $stockRole = $item->stock_role instanceof InventoryStockRole
                    ? $item->stock_role
                    : InventoryStockRole::tryFrom((string) $item->stock_role);

                $results->push([
                    'item' => $item,
                    'warehouse' => $warehouse,
                    'balance' => round($balance, 3),
                    'estimated_value' => round($balance * (float) $item->standard_cost, 2),
                    'last_movement_at' => $movementStats['last_movement_at'],
                    'last_outbound_at' => $movementStats['last_outbound_at'],
                    'days_inactive' => (int) $daysInactive,
                    'suggested_action' => $this->suggestAction($stockRole, $daysInactive),
                    'stock_role' => $stockRole?->value,
                ]);
            }
        }

        return $results
            ->sort(function (array $a, array $b) {
                $fgA = ($a['stock_role'] ?? '') === InventoryStockRole::FinishedGood->value ? 1 : 0;
                $fgB = ($b['stock_role'] ?? '') === InventoryStockRole::FinishedGood->value ? 1 : 0;

                if ($fgA !== $fgB) {
                    return $fgB <=> $fgA;
                }

                return ($b['estimated_value'] <=> $a['estimated_value']);
            })
            ->values();
    }

    /**
     * @return array{last_movement_at: Carbon|null, last_outbound_at: Carbon|null}
     */
    protected function movementStats(int $itemId, int $warehouseId, int $companyId): array
    {
        $movements = InventoryMovement::query()
            ->where('company_id', $companyId)
            ->where('inventory_item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->get(['movement_type', 'quantity', 'movement_date']);

        $lastMovement = null;
        $lastOutbound = null;

        foreach ($movements as $movement) {
            $type = $movement->movement_type instanceof InventoryMovementType
                ? $movement->movement_type
                : InventoryMovementType::from((string) $movement->movement_type);
            $qty = (float) $movement->quantity;
            $date = Carbon::parse($movement->movement_date);

            $lastMovement ??= $date;

            if (InventoryMovementAnalytics::isOutboundMovement($type, $qty)
                || ($type === InventoryMovementType::Adjustment && $qty < 0)) {
                $lastOutbound ??= $date;
                break;
            }
        }

        return [
            'last_movement_at' => $lastMovement,
            'last_outbound_at' => $lastOutbound,
        ];
    }

    protected function suggestAction(?InventoryStockRole $stockRole, int $daysInactive): DeadStockSuggestedAction
    {
        return match ($stockRole) {
            InventoryStockRole::FinishedGood => $daysInactive >= 120
                ? DeadStockSuggestedAction::Discount
                : DeadStockSuggestedAction::Promote,
            InventoryStockRole::RawMaterial, InventoryStockRole::Consumable, InventoryStockRole::Packaging => DeadStockSuggestedAction::Transfer,
            InventoryStockRole::AssetSpare => DeadStockSuggestedAction::Inspect,
            default => $daysInactive >= 180
                ? DeadStockSuggestedAction::WriteDownReview
                : DeadStockSuggestedAction::Inspect,
        };
    }
}
