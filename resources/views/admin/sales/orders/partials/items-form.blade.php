<div id="order-items" class="space-y-3">
    @php($rows = old('items', $salesOrder->items->map(fn ($i) => [
        'item_name' => $i->item_name,
        'description' => $i->description,
        'quantity' => $i->quantity,
        'unit_price' => $i->unit_price,
    ])->toArray() ?: [['item_name' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]]))

    @foreach ($rows as $index => $row)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 border-b pb-3">
            <input type="text" name="items[{{ $index }}][item_name]" class="erp-input" placeholder="{{ __('Item') }}" value="{{ $row['item_name'] ?? '' }}" required>
            <input type="text" name="items[{{ $index }}][description]" class="erp-input" placeholder="{{ __('Description') }}" value="{{ $row['description'] ?? '' }}">
            <input type="number" step="0.001" name="items[{{ $index }}][quantity]" class="erp-input" value="{{ $row['quantity'] ?? 1 }}" required>
            <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" class="erp-input" value="{{ $row['unit_price'] ?? 0 }}" required>
        </div>
    @endforeach
</div>
