<?php

namespace App\Support\Procurement;

use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;

class PurchaseOrderService
{
    public static function refreshReceivingStatus(PurchaseOrder $order): void
    {
        $order->loadMissing('items');

        $allReceived = $order->items->every(
            fn (PurchaseOrderItem $item) => (float) $item->quantity_received >= (float) $item->quantity,
        );
        $anyReceived = $order->items->contains(
            fn (PurchaseOrderItem $item) => (float) $item->quantity_received > 0,
        );

        if ($allReceived && $order->items->isNotEmpty()) {
            $order->update(['status' => \App\Enums\PurchaseOrderStatus::Received]);

            return;
        }

        if ($anyReceived) {
            $order->update(['status' => \App\Enums\PurchaseOrderStatus::PartiallyReceived]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return array<string, float>
     */
    public static function recalculateTotals(PurchaseOrder $order, array $lines): array
    {
        $subtotal = collect($lines)->sum(fn (array $line) => (float) ($line['line_total'] ?? 0));

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round((float) $order->tax_amount, 2),
            'discount_amount' => round((float) $order->discount_amount, 2),
            'total_amount' => round($subtotal + (float) $order->tax_amount - (float) $order->discount_amount, 2),
        ];
    }
}
