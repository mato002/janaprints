<?php

namespace App\Support\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequestService
{
    public static function submit(PurchaseRequest $request): PurchaseRequest
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

        $request->update(['status' => PurchaseRequestStatus::Submitted]);

        return $request->fresh(['items']);
    }

    public static function approve(PurchaseRequest $request): PurchaseRequest
    {
        if (! $request->status->canApprove()) {
            throw ValidationException::withMessages([
                'status' => __('Only submitted requests can be approved.'),
            ]);
        }

        $request->update(['status' => PurchaseRequestStatus::Approved]);

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
