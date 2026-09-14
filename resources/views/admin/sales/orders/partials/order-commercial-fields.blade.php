@php
    $line = isset($salesOrder) ? $salesOrder->items->first() : null;
    $quantityValue = old('quantity', $quantityValue ?? $line?->quantity ?? 1);
    $unitPriceValue = old('unit_price', $unitPriceValue ?? $line?->unit_price ?? 0);
    $idPrefix = $idPrefix ?? 'order';
@endphp

<div
    class="grid grid-cols-1 gap-3 sm:grid-cols-3"
    x-data="{
        qty: @js((string) $quantityValue),
        price: @js((string) $unitPriceValue),
        get total() {
            const qty = Number(this.qty) || 0;
            const price = Number(this.price) || 0;
            if (qty <= 0 || price <= 0) {
                return '';
            }
            return (qty * price).toFixed(2);
        },
    }"
>
    <div>
        <label class="erp-label" for="{{ $idPrefix }}-quantity">{{ __('Quantity') }}</label>
        <input
            id="{{ $idPrefix }}-quantity"
            type="number"
            name="quantity"
            class="erp-input w-full"
            min="0.001"
            step="any"
            required
            x-model="qty"
            value="{{ $quantityValue }}"
        >
    </div>
    <div>
        <label class="erp-label" for="{{ $idPrefix }}-unit-price">{{ __('Unit price') }}</label>
        <input
            id="{{ $idPrefix }}-unit-price"
            type="number"
            name="unit_price"
            class="erp-input w-full"
            min="0"
            step="0.01"
            required
            x-model="price"
            value="{{ $unitPriceValue }}"
        >
    </div>
    <div>
        <label class="erp-label">{{ __('Total') }}</label>
        <input type="text" class="erp-input w-full bg-slate-50" readonly :value="total">
        <p class="mt-1 text-xs text-slate-500">{{ __('Quantity × unit price') }}</p>
    </div>
</div>
