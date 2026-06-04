<?php

namespace App\Support\Procurement;

use App\Enums\DocumentType;
use App\Enums\SupplierQuotationStatus;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\RfqVendor;
use App\Models\Procurement\SupplierQuotation;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Facades\DB;

class RfqQuotationSyncService
{
    public static function syncFromVendorResponse(RfqVendor $rfqVendor): SupplierQuotation
    {
        return DB::transaction(function () use ($rfqVendor) {
            $rfq = $rfqVendor->rfq()->with(['items', 'purchaseRequest'])->firstOrFail();
            $rfqVendor->load(['responses.rfqItem', 'vendor']);

            $quotation = SupplierQuotation::query()->firstOrNew([
                'company_id' => $rfq->company_id,
                'branch_id' => $rfq->branch_id,
                'rfq_id' => $rfq->id,
                'vendor_id' => $rfqVendor->vendor_id,
            ]);

            if (! $quotation->exists) {
                $quotation->fill([
                    'quotation_number' => app(NumberingService::class)->next(
                        DocumentType::SupplierQuotation,
                        $rfq->company_id,
                        $rfq->branch_id,
                    ),
                    'quotation_date' => now()->toDateString(),
                    'valid_until' => $rfq->closing_date,
                    'status' => SupplierQuotationStatus::Received,
                    'purchase_request_id' => $rfq->purchase_request_id,
                    'notes' => __('Synced from RFQ :number', ['number' => $rfq->rfq_number]),
                ]);
            }

            $quotation->status = SupplierQuotationStatus::Received;
            $quotation->save();

            $quotation->items()->delete();
            $subtotal = 0.0;

            foreach ($rfq->items as $rfqItem) {
                $response = $rfqVendor->responses->firstWhere('rfq_item_id', $rfqItem->id);
                $unitCost = (float) ($response?->quoted_price ?? 0);
                $lineTotal = $unitCost * (float) $rfqItem->quantity;
                $subtotal += $lineTotal;

                $quotation->items()->create([
                    'inventory_item_id' => $rfqItem->inventory_item_id,
                    'purchase_request_item_id' => $rfqItem->purchase_request_item_id,
                    'description' => $rfqItem->description,
                    'quantity' => $rfqItem->quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                    'lead_time_days' => $response?->lead_time_days,
                    'warranty' => $response?->warranty,
                    'delivery_terms' => $response?->delivery_terms,
                ]);
            }

            $quotation->update([
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total_amount' => $subtotal,
            ]);

            return $quotation->fresh(['items', 'vendor']);
        });
    }

    public static function syncAll(Rfq $rfq): int
    {
        $count = 0;

        foreach ($rfq->vendors()->where('invitation_status', 'responded')->get() as $rfqVendor) {
            self::syncFromVendorResponse($rfqVendor);
            $count++;
        }

        return $count;
    }
}
