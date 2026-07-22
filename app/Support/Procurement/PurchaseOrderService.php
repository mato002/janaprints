<?php

namespace App\Support\Procurement;

use App\Enums\ApprovalRuleType;
use App\Enums\PurchaseOrderStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public static function submit(PurchaseOrder $order, int $userId): PurchaseOrder
    {
        if ($order->status !== PurchaseOrderStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft purchase orders can be submitted.'),
            ]);
        }

        $amount = (float) $order->total_amount;
        $coordinator = app(ProcurementGovernanceCoordinator::class);

        $autoApproved = $coordinator->submit(
            $order,
            ApprovalRuleType::ProcurementApproval,
            $amount,
            $userId,
            'purchase_order_submitted',
        );

        if ($autoApproved) {
            $order->update([
                'status' => PurchaseOrderStatus::Approved,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
        } else {
            $order->update(['status' => PurchaseOrderStatus::PendingApproval]);
        }

        return $order->fresh(['items', 'vendor']);
    }

    public static function approve(PurchaseOrder $order, User $actor, ?string $notes = null): PurchaseOrder
    {
        if ($order->status !== PurchaseOrderStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => __('Only pending purchase orders can be approved.'),
            ]);
        }

        $coordinator = app(ProcurementGovernanceCoordinator::class);

        $complete = $coordinator->approve(
            $order,
            ApprovalRuleType::ProcurementApproval,
            $actor,
            $notes,
            'procurement.orders.approve',
            (int) $order->prepared_by,
            'purchase_approved',
            'purchase_order_approval_step',
        );

        if ($complete) {
            $order->update([
                'status' => PurchaseOrderStatus::Approved,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
        }

        return $order->fresh(['items', 'vendor']);
    }

    public static function reject(PurchaseOrder $order, User $actor, string $reason): PurchaseOrder
    {
        if ($order->status !== PurchaseOrderStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => __('Only pending purchase orders can be rejected.'),
            ]);
        }

        $coordinator = app(ProcurementGovernanceCoordinator::class);

        $coordinator->reject(
            $order,
            ApprovalRuleType::ProcurementApproval,
            $actor,
            $reason,
            'procurement.orders.approve',
            (int) $order->prepared_by,
            'purchase_order_rejected',
        );

        $order->update(['status' => PurchaseOrderStatus::Rejected]);

        return $order->fresh(['items', 'vendor']);
    }

    public static function assertCanSend(PurchaseOrder $order): void
    {
        if ($order->status !== PurchaseOrderStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => __('Purchase order must be approved before it can be sent.'),
            ]);
        }

        app(ProcurementGovernanceCoordinator::class)->assertChainApprovedForPosting(
            $order,
            ApprovalRuleType::ProcurementApproval,
            (float) $order->total_amount,
            __('Purchase order approval chain must be completed before sending.'),
        );
    }

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
            $order->update(['status' => PurchaseOrderStatus::Received]);

            return;
        }

        if ($anyReceived) {
            $order->update(['status' => PurchaseOrderStatus::PartiallyReceived]);
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
