<?php

namespace App\Support\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\RfqStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\RfqItem;
use App\Models\Procurement\RfqVendor;
use App\Models\Procurement\RfqVendorResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RFQService
{
    public static function createFromPurchaseRequest(
        PurchaseRequest $request,
        string $rfqNumber,
        int $userId,
        ?string $closingDate = null,
        array $vendorIds = [],
    ): Rfq {
        if ($request->status !== PurchaseRequestStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => __('Only approved purchase requests can spawn an RFQ.'),
            ]);
        }

        return DB::transaction(function () use ($request, $rfqNumber, $userId, $closingDate, $vendorIds) {
            $request->load('items');

            $rfq = Rfq::query()->create([
                'company_id' => $request->company_id,
                'branch_id' => $request->branch_id,
                'purchase_request_id' => $request->id,
                'rfq_number' => $rfqNumber,
                'issue_date' => now()->toDateString(),
                'closing_date' => $closingDate,
                'status' => RfqStatus::Draft,
                'notes' => $request->notes,
                'created_by' => $userId,
            ]);

            foreach ($request->items as $item) {
                RfqItem::query()->create([
                    'rfq_id' => $rfq->id,
                    'purchase_request_item_id' => $item->id,
                    'inventory_item_id' => $item->inventory_item_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                ]);
            }

            foreach ($vendorIds as $vendorId) {
                RfqVendor::query()->create([
                    'rfq_id' => $rfq->id,
                    'vendor_id' => $vendorId,
                    'invitation_status' => 'invited',
                    'invited_at' => now(),
                ]);
            }

            return $rfq->fresh(['items', 'vendors.vendor']);
        });
    }

    public static function issue(Rfq $rfq): Rfq
    {
        if (! $rfq->status->canIssue()) {
            throw ValidationException::withMessages([
                'status' => __('Only draft RFQs can be issued.'),
            ]);
        }

        if ($rfq->vendors()->count() < 1) {
            throw ValidationException::withMessages([
                'vendors' => __('At least one vendor must be invited.'),
            ]);
        }

        $rfq->update(['status' => RfqStatus::Open]);

        return $rfq->fresh();
    }

    public static function close(Rfq $rfq): Rfq
    {
        if (! in_array($rfq->status, [RfqStatus::Open], true)) {
            throw ValidationException::withMessages([
                'status' => __('Only open RFQs can be closed for comparison.'),
            ]);
        }

        $rfq->update(['status' => RfqStatus::AwaitingComparison]);

        return $rfq->fresh();
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    public static function recordVendorResponse(RfqVendor $rfqVendor, array $lines): RfqVendor
    {
        if (! $rfqVendor->rfq->status->canReceiveResponses()) {
            throw ValidationException::withMessages([
                'status' => __('RFQ is not accepting vendor responses.'),
            ]);
        }

        return DB::transaction(function () use ($rfqVendor, $lines) {
            foreach ($lines as $line) {
                RfqVendorResponse::query()->updateOrCreate(
                    [
                        'rfq_vendor_id' => $rfqVendor->id,
                        'rfq_item_id' => $line['rfq_item_id'],
                    ],
                    [
                        'rfq_id' => $rfqVendor->rfq_id,
                        'quoted_price' => $line['quoted_price'],
                        'lead_time_days' => $line['lead_time_days'] ?? null,
                        'warranty' => $line['warranty'] ?? null,
                        'delivery_terms' => $line['delivery_terms'] ?? null,
                        'comments' => $line['comments'] ?? null,
                    ],
                );
            }

            $rfqVendor->update([
                'invitation_status' => 'responded',
                'responded_at' => now(),
            ]);

            return $rfqVendor->fresh(['responses.rfqItem', 'vendor']);
        });
    }

    public static function award(Rfq $rfq, int $vendorId): Rfq
    {
        if (! $rfq->status->canAward()) {
            throw ValidationException::withMessages([
                'status' => __('RFQ cannot be awarded in its current status.'),
            ]);
        }

        $rfq->update([
            'status' => RfqStatus::Awarded,
            'awarded_vendor_id' => $vendorId,
        ]);

        return $rfq->fresh();
    }

    public static function convertToPurchaseOrder(
        Rfq $rfq,
        string $poNumber,
        int $userId,
    ): PurchaseOrder {
        if (! $rfq->status->canConvert()) {
            throw ValidationException::withMessages([
                'status' => __('Only awarded RFQs can convert to a purchase order.'),
            ]);
        }

        return DB::transaction(function () use ($rfq, $poNumber, $userId) {
            $rfq->load(['items', 'awardedVendor']);

            $awardedVendor = $rfq->vendors()
                ->where('vendor_id', $rfq->awarded_vendor_id)
                ->firstOrFail();

            $responses = RfqVendorResponse::query()
                ->where('rfq_vendor_id', $awardedVendor->id)
                ->get()
                ->keyBy('rfq_item_id');

            $subtotal = 0.0;

            $order = PurchaseOrder::query()->create([
                'company_id' => $rfq->company_id,
                'branch_id' => $rfq->branch_id,
                'vendor_id' => $rfq->awarded_vendor_id,
                'purchase_request_id' => $rfq->purchase_request_id,
                'po_number' => $poNumber,
                'order_date' => now()->toDateString(),
                'status' => PurchaseOrderStatus::Draft,
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'prepared_by' => $userId,
                'notes' => __('Created from RFQ :number', ['number' => $rfq->rfq_number]),
            ]);

            foreach ($rfq->items as $item) {
                $response = $responses->get($item->id);
                $unitCost = (float) ($response?->quoted_price ?? 0);
                $lineTotal = $unitCost * (float) $item->quantity;
                $subtotal += $lineTotal;

                $order->items()->create([
                    'inventory_item_id' => $item->inventory_item_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
            ]);

            $rfq->update([
                'status' => RfqStatus::ConvertedToPo,
                'purchase_order_id' => $order->id,
            ]);

            if ($rfq->purchase_request_id) {
                PurchaseRequest::query()
                    ->whereKey($rfq->purchase_request_id)
                    ->update(['status' => PurchaseRequestStatus::ConvertedToPo]);
            }

            return $order->fresh(['items', 'vendor']);
        });
    }
}
