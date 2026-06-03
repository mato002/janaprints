@php($m = $item ?? null)
@php($fields = $formFields ?? [])
@if(($fields['inventory_category_id']['visible'] ?? true))
<div><label class="erp-label">{{ __('Category') }}</label>
    <select name="inventory_category_id" class="erp-input w-full" @required($fields['inventory_category_id']['required'] ?? true) @disabled($fields['inventory_category_id']['read_only'] ?? false)>
        @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('inventory_category_id', $m?->inventory_category_id) == $c->id)>{{ $c->name }}</option>@endforeach
    </select></div>
@endif
@if(($fields['unit_of_measure_id']['visible'] ?? true))
<div><label class="erp-label">{{ __('Unit') }}</label>
    <select name="unit_of_measure_id" class="erp-input w-full" @required($fields['unit_of_measure_id']['required'] ?? true) @disabled($fields['unit_of_measure_id']['read_only'] ?? false)>
        @foreach ($units as $u)<option value="{{ $u->id }}" @selected(old('unit_of_measure_id', $m?->unit_of_measure_id) == $u->id)>{{ $u->name }}</option>@endforeach
    </select></div>
@endif
@if(($fields['sku']['visible'] ?? true))
<div><label class="erp-label">{{ __('SKU') }}</label><input name="sku" class="erp-input w-full" value="{{ old('sku', $m?->sku ?? ($fields['sku']['default'] ?? '')) }}" @required($fields['sku']['required'] ?? true) @readonly($fields['sku']['read_only'] ?? false)></div>
@endif
@if(($fields['item_name']['visible'] ?? true))
<div><label class="erp-label">{{ __('Name') }}</label><input name="item_name" class="erp-input w-full" value="{{ old('item_name', $m?->item_name ?? ($fields['item_name']['default'] ?? '')) }}" @required($fields['item_name']['required'] ?? true) @readonly($fields['item_name']['read_only'] ?? false)></div>
@endif
@if(($fields['description']['visible'] ?? true))
<div><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" @required($fields['description']['required'] ?? false) @readonly($fields['description']['read_only'] ?? false)>{{ old('description', $m?->description ?? ($fields['description']['default'] ?? '')) }}</textarea></div>
@endif
@if(($fields['reorder_level']['visible'] ?? true) || ($fields['reorder_quantity']['visible'] ?? true) || ($fields['standard_cost']['visible'] ?? true))
<div class="grid grid-cols-3 gap-4">
    @if(($fields['reorder_level']['visible'] ?? true))
    <div><label class="erp-label">{{ __('Reorder level') }}</label><input type="number" step="0.001" name="reorder_level" class="erp-input w-full" value="{{ old('reorder_level', $m?->reorder_level ?? ($fields['reorder_level']['default'] ?? 0)) }}" @required($fields['reorder_level']['required'] ?? true) @readonly($fields['reorder_level']['read_only'] ?? false)></div>
    @endif
    @if(($fields['reorder_quantity']['visible'] ?? true))
    <div><label class="erp-label">{{ __('Reorder qty') }}</label><input type="number" step="0.001" name="reorder_quantity" class="erp-input w-full" value="{{ old('reorder_quantity', $m?->reorder_quantity ?? ($fields['reorder_quantity']['default'] ?? 0)) }}" @required($fields['reorder_quantity']['required'] ?? true) @readonly($fields['reorder_quantity']['read_only'] ?? false)></div>
    @endif
    @if(($fields['standard_cost']['visible'] ?? true))
    <div><label class="erp-label">{{ __('Standard cost') }}</label><input type="number" step="0.01" name="standard_cost" class="erp-input w-full" value="{{ old('standard_cost', $m?->standard_cost ?? ($fields['standard_cost']['default'] ?? 0)) }}" @required($fields['standard_cost']['required'] ?? true) @readonly($fields['standard_cost']['read_only'] ?? false)></div>
    @endif
</div>
@endif
