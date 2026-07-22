<?php

namespace App\Support\Procurement;

use App\Enums\ApprovalRuleType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequestService
{
    public static function totalAmount(PurchaseRequest $request): float
    {
        $request->loadMissing('items');

        return round($request->items->sum(fn ($item) => (float) $item->line_total), 2);
    }

    public static function requiresApproval(PurchaseRequest $request): bool
    {
        return app(ProcurementGovernanceCoordinator::class)->requiresApproval(
            ApprovalRuleType::PurchaseRequestApproval,
            self::totalAmount($request),
            (int) $request->company_id,
            $request->branch_id,
        );
    }

    public static function submit(PurchaseRequest $request, int $userId): PurchaseRequest
    {
        if (! $request->status->canSubmit()) {
            throw ValidationException::withMessages([
                'status' => __('Only draft requests can be submitted.'),
            ]);
        }

        if ($request->items()->count() < 1) {
            throw ValidationException::withMessages([
                'items' => __('Purchase request must have at least one line.'),
            ]);
        }

        $amount = self::totalAmount($request);
        $coordinator = app(ProcurementGovernanceCoordinator::class);

        $autoApproved = $coordinator->submit(
            $request,
            ApprovalRuleType::PurchaseRequestApproval,
            $amount,
            $userId,
            'purchase_request_submitted',
        );

        if ($autoApproved) {
            $request->update([
                'total_amount' => $amount,
                'status' => PurchaseRequestStatus::Approved,
                'submitted_by' => $userId,
                'submitted_at' => now(),
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
        } else {
            $request->update([
                'total_amount' => $amount,
                'status' => PurchaseRequestStatus::PendingApproval,
                'submitted_by' => $userId,
                'submitted_at' => now(),
            ]);
        }

        return $request->fresh(['items']);
    }

    public static function approve(PurchaseRequest $request, User $actor, ?string $notes = null): PurchaseRequest
    {
        if (! $request->status->canApprove()) {
            throw ValidationException::withMessages([
                'status' => __('Only submitted requests can be approved.'),
            ]);
        }

        $amount = (float) ($request->total_amount ?: self::totalAmount($request));
        $coordinator = app(ProcurementGovernanceCoordinator::class);

        $complete = $coordinator->approve(
            $request,
            ApprovalRuleType::PurchaseRequestApproval,
            $actor,
            $notes,
            'procurement.requests.approve',
            (int) $request->requested_by,
            'purchase_request_approved',
            'purchase_request_approval_step',
        );

        if ($complete) {
            $request->update([
                'status' => PurchaseRequestStatus::Approved,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
        }

        return $request->fresh(['items']);
    }

    public static function reject(PurchaseRequest $request, User $actor, string $reason): PurchaseRequest
    {
        if (! $request->status->canReject()) {
            throw ValidationException::withMessages([
                'status' => __('Only pending requests can be rejected.'),
            ]);
        }

        $coordinator = app(ProcurementGovernanceCoordinator::class);

        $coordinator->reject(
            $request,
            ApprovalRuleType::PurchaseRequestApproval,
            $actor,
            $reason,
            'procurement.requests.approve',
            (int) $request->requested_by,
            'purchase_request_rejected',
        );

        $request->update([
            'status' => PurchaseRequestStatus::Rejected,
            'rejected_by' => $actor->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $request->fresh(['items']);
    }

    public static function convertToPurchaseOrder(
        PurchaseRequest $request,
        int $vendorId,
        int $userId,
        string $poNumber,
    ): PurchaseOrder {
        if (! $request->status->canConvert()) {
            throw ValidationException::withMessages([
                'status' => __('Only approved requests can be converted.'),
            ]);
        }

        app(ProcurementGovernanceCoordinator::class)->assertChainApproved(
            $request,
            __('Purchase request approval must be completed before conversion.'),
        );

        return DB::transaction(function () use ($request, $vendorId, $userId, $poNumber) {
            $request->load('items');

            $subtotal = $request->items->sum(fn ($item) => (float) $item->line_total);

            $order = PurchaseOrder::query()->create([
                'company_id' => $request->company_id,
                'branch_id' => $request->branch_id,
                'vendor_id' => $vendorId,
                'purchase_request_id' => $request->id,
                'po_number' => $poNumber,
                'order_date' => now()->toDateString(),
                'expected_delivery_date' => $request->required_date,
                'status' => PurchaseOrderStatus::Draft,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'prepared_by' => $userId,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $order->items()->create([
                    'inventory_item_id' => $item->inventory_item_id,
                    'item_classification' => $item->item_classification,
                    'asset_category_id' => $item->asset_category_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->estimated_unit_cost,
                    'line_total' => $item->line_total,
                ]);
            }

            $request->update(['status' => PurchaseRequestStatus::ConvertedToPo]);

            return $order->fresh(['items', 'vendor']);
        });
    }
}
