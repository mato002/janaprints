@php
    $rows = old('items', isset($quotation) ? $quotation->items->map(fn ($i) => [
        'item_type' => $i->item_type->value,
        'item_name' => $i->item_name,
        'description' => $i->description,
        'quantity' => $i->quantity,
        'unit_price' => $i->unit_price,
        'discount' => $i->discount,
        'tax_rate' => $i->tax_rate,
    ])->toArray() : [['item_type' => 'product', 'item_name' => '', 'quantity' => 1, 'unit_price' => 0, 'discount' => 0, 'tax_rate' => 16]]);
@endphp

<div class="space-y-3" x-data="{ rows: @js($rows) }">
    <template x-for="(row, index) in rows" :key="index">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-type-'+index">{{ __('Type') }}</label>
                <select
                    :id="'quotation-item-type-'+index"
                    :name="'items['+index+'][item_type]'"
                    class="erp-input w-full"
                    x-model="row.item_type"
                >
                    @foreach ($itemTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0 sm:col-span-2 lg:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-name-'+index">{{ __('Item name') }}</label>
                <input
                    type="text"
                    :id="'quotation-item-name-'+index"
                    :name="'items['+index+'][item_name]'"
                    class="erp-input w-full"
                    placeholder="{{ __('e.g. A5 Flyers — 2000 pcs') }}"
                    x-model="row.item_name"
                    required
                >
            </div>
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-qty-'+index">{{ __('Quantity') }}</label>
                <input
                    type="number"
                    step="0.001"
                    min="0.001"
                    :id="'quotation-item-qty-'+index"
                    :name="'items['+index+'][quantity]'"
                    class="erp-input w-full"
                    x-model="row.quantity"
                    required
                >
            </div>
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-price-'+index">{{ __('Unit price') }}</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    :id="'quotation-item-price-'+index"
                    :name="'items['+index+'][unit_price]'"
                    class="erp-input w-full"
                    x-model="row.unit_price"
                    required
                >
            </div>
            <input type="hidden" :name="'items['+index+'][discount]'" x-model="row.discount">
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-tax-'+index">{{ __('Tax (%)') }}</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    :id="'quotation-item-tax-'+index"
                    :name="'items['+index+'][tax_rate]'"
                    class="erp-input w-full"
                    x-model="row.tax_rate"
                >
            </div>
        </div>
    </template>

    <button
        type="button"
        class="erp-btn-secondary text-sm"
        @click="rows.push({item_type:'product',item_name:'',quantity:1,unit_price:0,discount:0,tax_rate:16})"
    >{{ __('Add line') }}</button>
</div>
