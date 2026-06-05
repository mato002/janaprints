<?php

namespace App\Support\Procurement;

use App\Enums\DocumentType;
use App\Enums\GoodsReceiptStatus;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockReceiptSource;
use App\Models\Inventory\StockReceipt;
use App\Models\Procurement\GoodsReceipt;
use App\Services\Assets\AssetCapitalizationService;
use App\Support\Platform\NumberingService;
use App\Support\Accounting\InventoryAccountingPostingService;
use App\Support\StockReceiptService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public static function post(GoodsReceipt $goodsReceipt, int $userId): GoodsReceipt
    {
        if ($goodsReceipt->status === GoodsReceiptStatus::Posted) {
            throw ValidationException::withMessages([
                'receipt' => __('Goods receipt already posted.'),
            ]);
        }

        if ($goodsReceipt->items()->count() < 1) {
            throw ValidationException::withMessages([
                'items' => __('Goods receipt must have at least one line.'),
            ]);
        }

        return DB::transaction(function () use ($goodsReceipt, $userId) {
            $goodsReceipt->load(['items.purchaseOrderItem', 'purchaseOrder', 'warehouse']);

            if (! $goodsReceipt->purchaseOrder->status->canReceive()) {
                throw ValidationException::withMessages([
                    'purchase_order' => __('Purchase order is not open for receiving.'),
                ]);
            }

            $capitalization = app(AssetCapitalizationService::class);
            $inventoryLines = $goodsReceipt->items->filter(fn ($line) => ! AssetCapitalizationService::isCapitalLine($line));
            $capitalLines = $goodsReceipt->items->filter(fn ($line) => AssetCapitalizationService::isCapitalLine($line));

            foreach ($goodsReceipt->items as $line) {
                $remaining = $line->purchaseOrderItem->remainingQuantity();

                if ((float) $line->quantity_received > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => __('Received quantity exceeds remaining order quantity.'),
                    ]);
                }
            }

            $stockReceipt = null;

            if ($inventoryLines->isNotEmpty()) {
                $stockReceipt = StockReceipt::query()->create([
                    'company_id' => $goodsReceipt->company_id,
                    'branch_id' => $goodsReceipt->branch_id,
                    'warehouse_id' => $goodsReceipt->warehouse_id,
                    'receipt_number' => app(NumberingService::class)->next(
                        DocumentType::StockReceipt,
                        $goodsReceipt->company_id,
                        $goodsReceipt->branch_id,
                    ),
                    'source' => StockReceiptSource::Purchase,
                    'receipt_date' => $goodsReceipt->receipt_date,
                    'status' => InventoryDocumentStatus::Draft,
                    'notes' => __('Procurement goods receipt :number', ['number' => $goodsReceipt->receipt_number]),
                    'received_by' => $userId,
                ]);

                foreach ($inventoryLines as $line) {
                    $stockLine = $stockReceipt->items()->create([
                        'inventory_item_id' => $line->inventory_item_id,
                        'quantity' => $line->quantity_received,
                        'unit_cost' => $line->unit_cost,
                    ]);

                    $line->update(['stock_receipt_item_id' => $stockLine->id]);
                }

                StockReceiptService::post($stockReceipt, $userId);
            }

            foreach ($goodsReceipt->items as $line) {
                $poItem = $line->purchaseOrderItem;
                $poItem->update([
                    'quantity_received' => (float) $poItem->quantity_received + (float) $line->quantity_received,
                ]);
            }

            foreach ($capitalLines as $line) {
                $capitalization->createCandidateFromReceiptLine($goodsReceipt, $line);
            }

            PurchaseOrderService::refreshReceivingStatus($goodsReceipt->purchaseOrder->fresh(['items']));

            $goodsReceipt->update([
                'stock_receipt_id' => $stockReceipt?->id,
                'status' => GoodsReceiptStatus::Posted,
                'posted_at' => now(),
            ]);

            if ($inventoryLines->isNotEmpty()) {
                app(InventoryAccountingPostingService::class)->postGoodsReceipt($goodsReceipt, $userId, $inventoryLines);
            }

            return $goodsReceipt->fresh(['items', 'stockReceipt', 'purchaseOrder']);
        });
    }
}
