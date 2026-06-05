<?php

namespace App\Support\Procurement;

use App\Enums\DocumentType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\RfqStatus;
use App\Enums\SupplierQuotationStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\RfqAwardLine;
use App\Models\Procurement\RfqVendor;
use App\Models\Procurement\RfqVendorResponse;
use App\Models\Procurement\SupplierQuotation;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RfqAwardService
{
    /**
     * @return array{rfq: Rfq, purchase_orders: Collection<int, PurchaseOrder>}
     */
    public static function awardFull(Rfq $rfq, int $vendorId, int $userId, bool $autoPo = true): array
    {
        self::assertVendorResponded($rfq, $vendorId);

        $lines = $rfq->items->map(fn ($item) => [
            'rfq_item_id' => $item->id,
            'quantity' => (float) $item->quantity,
        ])->all();

        return self::executeAward($rfq, 'full', $vendorId, $lines, $userId, $autoPo);
    }

    /**
     * @param  array<int, array{rfq_item_id: int, quantity: float}>  $lines
     * @return array{rfq: Rfq, purchase_orders: Collection<int, PurchaseOrder>}
     */
    public static function awardPartial(Rfq $rfq, int $vendorId, array $lines, int $userId, bool $autoPo = true): array
    {
        self::assertVendorResponded($rfq, $vendorId);
        self::validateQuantities($rfq, $lines);

        return self::executeAward($rfq, 'partial', $vendorId, $lines, $userId, $autoPo);
    }

    /**
     * @param  array<int, array{vendor_id: int, rfq_item_id: int, quantity: float}>  $allocations
     * @return array{rfq: Rfq, purchase_orders: Collection<int, PurchaseOrder>}
     */
    public static function splitAward(Rfq $rfq, array $allocations, int $userId, bool $autoPo = true): array
    {
        if (! $rfq->status->canAward()) {
            throw ValidationException::withMessages([
                'status' => __('RFQ cannot be awarded in its current status.'),
            ]);
        }

        $rfq->load('items');

        $byItem = collect($allocations)->groupBy('rfq_item_id');
        foreach ($rfq->items as $item) {
            $allocated = (float) collect($byItem->get($item->id, []))->sum('quantity');
            if (abs($allocated - (float) $item->quantity) > 0.001) {
                throw ValidationException::withMessages([
                    'allocations' => __('Split award must allocate the full required quantity for :item.', ['item' => $item->description]),
                ]);
            }
        }

        foreach ($allocations as $allocation) {
            self::assertVendorResponded($rfq, (int) $allocation['vendor_id']);
        }

        return DB::transaction(function () use ($rfq, $allocations, $userId, $autoPo) {
            $primaryVendorId = collect($allocations)
                ->sortByDesc(fn (array $row) => $row['quantity'])
                ->value('vendor_id');

            $rfq->update([
                'status' => RfqStatus::Awarded,
                'awarded_vendor_id' => $primaryVendorId,
                'award_type' => 'split',
            ]);

            $rfq->awardLines()->delete();

            foreach ($allocations as $allocation) {
                RfqAwardLine::query()->create([
                    'rfq_id' => $rfq->id,
                    'rfq_item_id' => $allocation['rfq_item_id'],
                    'vendor_id' => $allocation['vendor_id'],
                    'awarded_quantity' => $allocation['quantity'],
                    'award_type' => 'split',
                    'created_by' => $userId,
                ]);
            }

            self::syncQuotationStatuses($rfq, collect($allocations)->pluck('vendor_id')->unique()->all());

            $orders = $autoPo
                ? self::createPurchaseOrdersFromAwardLines($rfq->fresh(['awardLines.rfqItem']), $userId)
                : collect();

            if ($autoPo && $orders->isNotEmpty()) {
                $rfq->update([
                    'status' => RfqStatus::ConvertedToPo,
                    'purchase_order_id' => $orders->first()->id,
                ]);
                self::markPurchaseRequestConverted($rfq);
            }

            return [
                'rfq' => $rfq->fresh(['awardLines', 'awardedVendor', 'purchaseOrder']),
                'purchase_orders' => $orders,
            ];
        });
    }

    public static function rejectQuote(RfqVendor $rfqVendor): RfqVendor
    {
        return DB::transaction(function () use ($rfqVendor) {
            $rfqVendor->update(['invitation_status' => 'rejected']);

            SupplierQuotation::query()
                ->where('rfq_id', $rfqVendor->rfq_id)
                ->where('vendor_id', $rfqVendor->vendor_id)
                ->update(['status' => SupplierQuotationStatus::Rejected]);

            return $rfqVendor->fresh(['vendor']);
        });
    }

    public static function requestRequote(RfqVendor $rfqVendor): RfqVendor
    {
        return DB::transaction(function () use ($rfqVendor) {
            $rfqVendor->update([
                'invitation_status' => 'requote_requested',
                'responded_at' => null,
            ]);

            $rfqVendor->responses()->delete();

            SupplierQuotation::query()
                ->where('rfq_id', $rfqVendor->rfq_id)
                ->where('vendor_id', $rfqVendor->vendor_id)
                ->update(['status' => SupplierQuotationStatus::Draft]);

            return $rfqVendor->fresh(['vendor']);
        });
    }

    /**
     * @param  array<int, array{rfq_item_id: int, quantity: float}>  $lines
     * @return array{rfq: Rfq, purchase_orders: Collection<int, PurchaseOrder>}
     */
    protected static function executeAward(
        Rfq $rfq,
        string $awardType,
        int $vendorId,
        array $lines,
        int $userId,
        bool $autoPo,
    ): array {
        if (! $rfq->status->canAward()) {
            throw ValidationException::withMessages([
                'status' => __('RFQ cannot be awarded in its current status.'),
            ]);
        }

        return DB::transaction(function () use ($rfq, $awardType, $vendorId, $lines, $userId, $autoPo) {
            $rfq->update([
                'status' => RfqStatus::Awarded,
                'awarded_vendor_id' => $vendorId,
                'award_type' => $awardType,
            ]);

            $rfq->awardLines()->delete();

            foreach ($lines as $line) {
                RfqAwardLine::query()->create([
                    'rfq_id' => $rfq->id,
                    'rfq_item_id' => $line['rfq_item_id'],
                    'vendor_id' => $vendorId,
                    'awarded_quantity' => $line['quantity'],
                    'award_type' => $awardType,
                    'created_by' => $userId,
                ]);
            }

            self::syncQuotationStatuses($rfq, [$vendorId]);

            $orders = $autoPo
                ? self::createPurchaseOrdersFromAwardLines($rfq->fresh(['awardLines.rfqItem']), $userId)
                : collect();

            if ($autoPo && $orders->isNotEmpty()) {
                $rfq->update([
                    'status' => RfqStatus::ConvertedToPo,
                    'purchase_order_id' => $orders->first()->id,
                ]);
                self::markPurchaseRequestConverted($rfq);
            }

            return [
                'rfq' => $rfq->fresh(['awardLines', 'awardedVendor', 'purchaseOrder']),
                'purchase_orders' => $orders,
            ];
        });
    }

    /**
     * @return Collection<int, PurchaseOrder>
     */
    protected static function createPurchaseOrdersFromAwardLines(Rfq $rfq, int $userId): Collection
    {
        $rfq->load(['awardLines.rfqItem', 'awardLines.vendor']);

        $orders = collect();
        $numbering = app(NumberingService::class);

        foreach ($rfq->awardLines->groupBy('vendor_id') as $vendorId => $awardLines) {
            $vendor = $rfq->vendors()->where('vendor_id', $vendorId)->firstOrFail();
            $responses = RfqVendorResponse::query()
                ->where('rfq_vendor_id', $vendor->id)
                ->get()
                ->keyBy('rfq_item_id');

            $subtotal = 0.0;

            $order = PurchaseOrder::query()->create([
                'company_id' => $rfq->company_id,
                'branch_id' => $rfq->branch_id,
                'vendor_id' => $vendorId,
                'purchase_request_id' => $rfq->purchase_request_id,
                'po_number' => $numbering->next(DocumentType::PurchaseOrder, $rfq->company_id, $rfq->branch_id),
                'order_date' => now()->toDateString(),
                'status' => PurchaseOrderStatus::Draft,
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'prepared_by' => $userId,
                'notes' => __('Auto-generated from RFQ :number award', ['number' => $rfq->rfq_number]),
            ]);

            foreach ($awardLines as $awardLine) {
                $item = $awardLine->rfqItem;
                $response = $responses->get($awardLine->rfq_item_id);
                $unitCost = (float) ($response?->quoted_price ?? 0);
                $qty = (float) $awardLine->awarded_quantity;
                $lineTotal = $unitCost * $qty;
                $subtotal += $lineTotal;

                $order->items()->create([
                    'inventory_item_id' => $item->inventory_item_id,
                    'description' => $item->description,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);

                $awardLine->update(['purchase_order_id' => $order->id]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
            ]);

            $orders->push($order->fresh(['items', 'vendor']));
        }

        return $orders;
    }

    /**
     * @param  array<int, array{rfq_item_id: int, quantity: float}>  $lines
     */
    protected static function validateQuantities(Rfq $rfq, array $lines): void
    {
        $rfq->loadMissing('items');
        $required = $rfq->items->keyBy('id');

        foreach ($lines as $line) {
            $item = $required->get($line['rfq_item_id']);
            if (! $item) {
                throw ValidationException::withMessages([
                    'lines' => __('Invalid RFQ line selected.'),
                ]);
            }

            if ((float) $line['quantity'] <= 0 || (float) $line['quantity'] > (float) $item->quantity) {
                throw ValidationException::withMessages([
                    'lines' => __('Awarded quantity must be between zero and the required quantity.'),
                ]);
            }
        }
    }

    protected static function assertVendorResponded(Rfq $rfq, int $vendorId): void
    {
        $rfqVendor = $rfq->vendors()->where('vendor_id', $vendorId)->first();

        if (! $rfqVendor || $rfqVendor->invitation_status !== 'responded') {
            throw ValidationException::withMessages([
                'vendor_id' => __('Selected supplier must have submitted a quotation.'),
            ]);
        }
    }

    /**
     * @param  list<int>  $awardedVendorIds
     */
    protected static function syncQuotationStatuses(Rfq $rfq, array $awardedVendorIds): void
    {
        SupplierQuotation::query()
            ->where('rfq_id', $rfq->id)
            ->whereIn('vendor_id', $awardedVendorIds)
            ->update(['status' => SupplierQuotationStatus::Accepted]);

        SupplierQuotation::query()
            ->where('rfq_id', $rfq->id)
            ->whereNotIn('vendor_id', $awardedVendorIds)
            ->where('status', SupplierQuotationStatus::Received)
            ->update(['status' => SupplierQuotationStatus::Rejected]);
    }

    protected static function markPurchaseRequestConverted(Rfq $rfq): void
    {
        if ($rfq->purchase_request_id) {
            PurchaseRequest::query()
                ->whereKey($rfq->purchase_request_id)
                ->update(['status' => PurchaseRequestStatus::ConvertedToPo]);
        }
    }
}
