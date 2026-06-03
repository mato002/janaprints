<?php

namespace App\Support;

use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationRevision;

class QuotationRevisionService
{
    public static function snapshot(Quotation $quotation, ?int $userId = null): QuotationRevision
    {
        $quotation->load(['items', 'customer', 'lead']);

        return QuotationRevision::query()->updateOrCreate(
            [
                'quotation_id' => $quotation->id,
                'revision_number' => $quotation->revision_number,
            ],
            [
                'snapshot' => self::buildSnapshot($quotation),
                'created_by' => $userId ?? auth()->id(),
                'created_at' => now(),
            ],
        );
    }

    public static function createNextRevision(Quotation $quotation, ?int $userId = null): void
    {
        self::snapshot($quotation, $userId);

        $quotation->update([
            'revision_number' => $quotation->revision_number + 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function buildSnapshot(Quotation $quotation): array
    {
        return [
            'quotation' => $quotation->only([
                'company_id', 'branch_id', 'customer_id', 'lead_id', 'quotation_number',
                'quotation_date', 'valid_until', 'currency', 'subtotal', 'tax_amount',
                'discount_amount', 'total_amount', 'status', 'revision_number', 'notes',
            ]),
            'items' => $quotation->items->map->only([
                'item_type', 'item_name', 'description', 'quantity', 'unit_price',
                'discount', 'tax_rate', 'line_total', 'sort_order',
            ])->values()->all(),
        ];
    }
}
