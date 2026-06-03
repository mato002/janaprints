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
        <div class="grid grid-cols-1 md:grid-cols-6 gap-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
            <select :name="'items['+index+'][item_type]'" class="erp-input" x-model="row.item_type">
                @foreach ($itemTypes as $type)<option value="{{ $type->value }}">{{ $type->name }}</option>@endforeach
            </select>
            <input type="text" :name="'items['+index+'][item_name]'" class="erp-input md:col-span-2" placeholder="{{ __('Item name') }}" x-model="row.item_name" required>
            <input type="number" step="0.001" :name="'items['+index+'][quantity]'" class="erp-input" x-model="row.quantity" required>
            <input type="number" step="0.01" :name="'items['+index+'][unit_price]'" class="erp-input" x-model="row.unit_price" required>
            <input type="hidden" :name="'items['+index+'][discount]'" x-model="row.discount">
            <input type="number" step="0.01" :name="'items['+index+'][tax_rate]'" class="erp-input" x-model="row.tax_rate">
        </div>
    </template>
    <button type="button" class="erp-btn-secondary text-sm" @click="rows.push({item_type:'product',item_name:'',quantity:1,unit_price:0,discount:0,tax_rate:16})">{{ __('Add line') }}</button>
</div>
