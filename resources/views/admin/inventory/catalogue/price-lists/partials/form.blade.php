@php($m = $priceList ?? null)
@php($priceLines = isset($m) ? $m->items->values() : collect())
<div class="erp-form-grid">
    <div><label class="erp-label">{{ __('Name') }}</label><input name="name" class="erp-input w-full" value="{{ old('name', $m?->name) }}" required></div>
    <div><label class="erp-label">{{ __('Currency') }}</label><input name="currency" maxlength="3" class="erp-input w-full uppercase" value="{{ old('currency', $m?->currency ?? 'KES') }}" required></div>
    <div><label class="erp-label">{{ __('Effective date') }}</label><input type="date" name="effective_date" class="erp-input w-full" value="{{ old('effective_date', $m?->effective_date?->toDateString()) }}"></div>
    <div><label class="erp-label">{{ __('Status') }}</label><select name="status" class="erp-select w-full" required>@foreach (['active' => 'Active', 'draft' => 'Draft', 'inactive' => 'Inactive'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $m?->status ?? 'active') === $value)>{{ __($label) }}</option>@endforeach</select></div>
</div>
<div class="mt-4">
    <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Items') }}</h3>
    <div class="space-y-2">
        @for ($i = 0; $i < 8; $i++)
            @php($line = $priceLines->get($i))
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                <select name="items[{{ $i }}][inventory_item_id]" class="erp-select w-full">
                    <option value="">{{ __('Select item') }}</option>
                    @foreach ($items as $item)<option value="{{ $item->id }}" @selected(old("items.$i.inventory_item_id", $line?->inventory_item_id) == $item->id)>{{ $item->sku }} - {{ $item->item_name }}</option>@endforeach
                </select>
                <input type="number" step="0.01" min="0" name="items[{{ $i }}][price_override]" class="erp-input w-full" placeholder="{{ __('Price override') }}" value="{{ old(\"items.$i.price_override\", $line?->price_override) }}">
            </div>
        @endfor
    </div>
</div>
