<?php

namespace App\Support;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Models\Inventory\StockReceipt;
use App\Support\Accounting\InventoryAccountingPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockReceiptService
{
    public static function post(StockReceipt $receipt, int $userId): StockReceipt
    {
        if ($receipt->status === InventoryDocumentStatus::Posted) {
            throw ValidationException::withMessages([
                'receipt' => __('Receipt already posted.'),
            ]);
        }

        if ($receipt->items()->count() < 1) {
            throw ValidationException::withMessages([
                'items' => __('Receipt must have at least one line.'),
            ]);
        }

        if (! $receipt->warehouse?->is_active) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Deactivated warehouses cannot receive new stock.'),
            ]);
        }

        return DB::transaction(function () use ($receipt, $userId) {
            $receipt->load('items.inventoryItem');

            foreach ($receipt->items as $line) {
                InventoryMovementService::record([
                    'company_id' => $receipt->company_id,
                    'branch_id' => $receipt->branch_id,
                    'inventory_item_id' => $line->inventory_item_id,
                    'warehouse_id' => $receipt->warehouse_id,
                    'movement_type' => InventoryMovementType::Receipt,
                    'quantity' => InventoryMovementService::receiptQuantity((float) $line->quantity),
                    'unit_cost' => $line->unit_cost,
                    'reference_type' => StockReceipt::class,
                    'reference_id' => $receipt->id,
                    'movement_date' => $receipt->receipt_date,
                    'created_by' => $userId,
                ]);
            }

            $receipt->update([
                'status' => InventoryDocumentStatus::Posted,
                'posted_at' => now(),
            ]);

            app(InventoryAccountingPostingService::class)->postStockReceipt($receipt, $userId);

            return $receipt->fresh(['items', 'warehouse']);
        });
    }
}
