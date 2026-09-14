@php
    $rows = old('items', $salesOrder->items->map(fn ($i) => [
        'item_name' => $i->item_name,
        'description' => $i->description,
        'quantity' => $i->quantity,
        'unit_price' => $i->unit_price,
    ])->toArray() ?: [['item_name' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]]);
    $hidePrimaryCommercials = (bool) ($hidePrimaryCommercials ?? false);
@endphp

<div id="order-items" class="space-y-3">
    @foreach ($rows as $index => $row)
        <div class="grid grid-cols-1 gap-2 border-b border-slate-100 pb-3 md:grid-cols-2 {{ $hidePrimaryCommercials && $index === 0 ? 'lg:grid-cols-2' : 'lg:grid-cols-4' }}">
            <div>
                <label class="erp-label">{{ __('Item') }}</label>
                <input type="text" name="items[{{ $index }}][item_name]" class="erp-input w-full" value="{{ $row['item_name'] ?? '' }}" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Description') }}</label>
                <input type="text" name="items[{{ $index }}][description]" class="erp-input w-full" value="{{ $row['description'] ?? '' }}">
            </div>
            @if ($hidePrimaryCommercials && $index === 0)
                <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}">
                <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? 0 }}">
            @else
                <div>
                    <label class="erp-label">{{ __('Quantity') }}</label>
                    <input type="number" step="0.001" min="0.001" name="items[{{ $index }}][quantity]" class="erp-input w-full" value="{{ $row['quantity'] ?? 1 }}" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Unit price') }}</label>
                    <input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" class="erp-input w-full" value="{{ $row['unit_price'] ?? 0 }}" required>
                </div>
            @endif
        </div>
    @endforeach
</div>
