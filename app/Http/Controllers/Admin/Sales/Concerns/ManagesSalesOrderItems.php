<?php

namespace App\Http\Controllers\Admin\Sales\Concerns;

use App\Models\Sales\SalesOrder;
use Illuminate\Http\Request;

trait ManagesSalesOrderItems
{
    /**
     * @return array{items: array<int, array<string, mixed>>, totals: array<string, float>}
     */
    protected function validatedItems(Request $request): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $subtotal = 0.0;
        $lines = [];

        foreach ($validated['items'] as $index => $item) {
            $lineTotal = round((float) $item['quantity'] * (float) $item['unit_price'], 2);
            $lines[$index] = ['line_total' => $lineTotal];
            $subtotal += $lineTotal;
        }

        return [
            'items' => $validated['items'],
            'totals' => [
                'lines' => $lines,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => round($subtotal, 2),
            ],
        ];
    }

    protected function syncItems(SalesOrder $salesOrder, array $items, array $totals): void
    {
        $salesOrder->items()->delete();

        foreach ($items as $index => $item) {
            $salesOrder->items()->create([
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $totals['lines'][$index]['line_total'] ?? 0,
                'sort_order' => $index,
            ]);
        }

        $salesOrder->update([
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'discount_amount' => $totals['discount_amount'],
            'total_amount' => $totals['total_amount'],
        ]);
    }
}
