<?php

namespace App\Support;

use App\Enums\DocumentType;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationConversion;
use App\Models\Sales\SalesOrder;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationConversionService
{
    public static function convert(Quotation $quotation, int $convertedBy, array $attributes = []): SalesOrder
    {
        if ($quotation->salesOrder()->exists()) {
            throw ValidationException::withMessages([
                'quotation' => __('This quotation has already been converted to a sales order.'),
            ]);
        }

        $artworkRequest = ArtworkApprovalValidator::assertCanCreateFromQuotation($quotation);

        return DB::transaction(function () use ($quotation, $artworkRequest, $convertedBy) {
            $salesOrder = SalesOrder::query()->create([
                'company_id' => $quotation->company_id,
                'branch_id' => $quotation->branch_id,
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'artwork_request_id' => $artworkRequest->id,
                'order_number' => app(NumberingService::class)->next(
                    DocumentType::SalesOrder,
                    $quotation->company_id,
                    $quotation->branch_id,
                ),
                'order_date' => now()->toDateString(),
                'required_date' => $quotation->valid_until,
                'status' => SalesOrderStatus::Draft,
                'production_destination' => $attributes['production_destination'] ?? null,
                'subtotal' => $quotation->subtotal,
                'tax_amount' => $quotation->tax_amount,
                'discount_amount' => $quotation->discount_amount,
                'total_amount' => $quotation->total_amount,
                'notes' => $quotation->notes,
                'created_by' => $convertedBy,
            ]);

            foreach ($quotation->items as $index => $item) {
                $salesOrder->items()->create([
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                    'sort_order' => $index,
                ]);
            }

            QuotationConversion::query()->create([
                'company_id' => $quotation->company_id,
                'branch_id' => $quotation->branch_id,
                'quotation_id' => $quotation->id,
                'sales_order_id' => $salesOrder->id,
                'artwork_request_id' => $artworkRequest->id,
                'quotation_revision_number' => $quotation->revision_number,
                'artwork_version_number' => $artworkRequest->current_version,
                'converted_by' => $convertedBy,
            ]);

            $quotation->transitionTo(QuotationStatus::Converted);
            QuotationRevisionService::snapshot($quotation);

            return $salesOrder->load(['customer', 'quotation', 'artworkRequest', 'items']);
        });
    }
}
