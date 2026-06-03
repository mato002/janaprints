<?php

namespace App\Http\Controllers\Admin\Sales\Concerns;

use App\Enums\QuotationItemType;
use App\Models\Sales\Quotation;
use App\Support\QuotationCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait ManagesQuotationItems
{
    /**
     * @return array{items: array<int, array<string, mixed>>, totals: array<string, float>}
     */
    protected function validatedItems(Request $request): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', Rule::enum(QuotationItemType::class)],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $totals = QuotationCalculator::calculate($validated['items']);

        return ['items' => $validated['items'], 'totals' => $totals];
    }

    protected function syncItems(Quotation $quotation, array $items, array $totals): void
    {
        $quotation->items()->delete();

        foreach ($items as $index => $item) {
            $quotation->items()->create([
                'item_type' => $item['item_type'],
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'tax_rate' => $item['tax_rate'] ?? 0,
                'line_total' => $totals['lines'][$index]['line_total'] ?? 0,
                'sort_order' => $index,
            ]);
        }

        $quotation->update([
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'discount_amount' => $totals['discount_amount'],
            'total_amount' => $totals['total_amount'],
        ]);
    }
}
