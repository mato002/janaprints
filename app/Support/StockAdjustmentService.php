<?php

namespace App\Support;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\StockAdjustmentDirection;
use App\Models\Inventory\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public static function post(StockAdjustment $adjustment, int $userId): StockAdjustment
    {
        if ($adjustment->status === InventoryDocumentStatus::Posted) {
            throw ValidationException::withMessages([
                'adjustment' => __('Adjustment already posted.'),
            ]);
        }

        if ($adjustment->items()->count() < 1) {
            throw ValidationException::withMessages([
                'items' => __('Adjustment must have at least one line.'),
            ]);
        }

        if (! $adjustment->warehouse?->is_active) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Deactivated warehouses cannot be adjusted.'),
            ]);
        }

        return DB::transaction(function () use ($adjustment, $userId) {
            $adjustment->load('items.inventoryItem');

            foreach ($adjustment->items as $line) {
                $qty = (float) $line->quantity;
                $signed = $line->direction === StockAdjustmentDirection::Increase
                    ? InventoryMovementService::receiptQuantity($qty)
                    : InventoryMovementService::issueQuantity($qty);

                if ($line->direction === StockAdjustmentDirection::Decrease) {
                    InventoryStockService::assertSufficientStock(
                        $line->inventory_item_id,
                        $adjustment->warehouse_id,
                        $qty,
                    );
                }

                InventoryMovementService::record([
                    'company_id' => $adjustment->company_id,
                    'branch_id' => $adjustment->branch_id,
                    'inventory_item_id' => $line->inventory_item_id,
                    'warehouse_id' => $adjustment->warehouse_id,
                    'movement_type' => InventoryMovementType::Adjustment,
                    'quantity' => $signed,
                    'unit_cost' => $line->unit_cost,
                    'reference_type' => StockAdjustment::class,
                    'reference_id' => $adjustment->id,
                    'movement_date' => $adjustment->adjustment_date,
                    'created_by' => $userId,
                ]);
            }

            $adjustment->update([
                'status' => InventoryDocumentStatus::Posted,
                'posted_at' => now(),
            ]);

            return $adjustment->fresh(['items', 'warehouse']);
        });
    }
}
