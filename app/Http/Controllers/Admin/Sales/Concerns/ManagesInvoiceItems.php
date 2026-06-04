<?php

namespace App\Http\Controllers\Admin\Sales\Concerns;

use App\Support\Sales\InvoiceCalculator;
use Illuminate\Http\Request;

trait ManagesInvoiceItems
{
    /**
     * @return array{items: array<int, array<string, mixed>>, calculated: array<string, mixed>}
     */
    protected function validatedInvoiceItems(Request $request): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['nullable', 'integer'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'header_discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $calculated = InvoiceCalculator::calculate(
            $validated['items'],
            (float) ($validated['header_discount'] ?? 0),
        );

        return [
            'items' => $validated['items'],
            'calculated' => $calculated,
        ];
    }
}
