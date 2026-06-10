@php($m = $item ?? null)
@php($fields = $formFields ?? [])

@if(($fields['inventory_category_id']['visible'] ?? true))
<x-admin.lookup-select
    name="inventory_category_id"
    :label="__('Category')"
    :options="$categories"
    :value="old('inventory_category_id', $m?->inventory_category_id)"
    :required="($fields['inventory_category_id']['required'] ?? true)"
    :readonly="($fields['inventory_category_id']['read_only'] ?? false)"
    create-route="admin.inventory.catalogue.categories.quick-create"
    refresh-route="admin.lookups.categories"
    permission="catalogue.create"
    :modal-title="__('Create category')"
    select-class="erp-input w-full"
    :empty-option="false"
/>
@endif

@php($subcategoryOptions = $subcategories->map(fn ($s) => ['value' => $s->id, 'label' => trim(($s->category?->name ? $s->category->name.' / ' : '').$s->name)])->values())

<x-admin.lookup-select
    name="subcategory_id"
    :label="__('Subcategory')"
    :options="$subcategoryOptions"
    :value="old('subcategory_id', $m?->subcategory_id)"
    create-route="admin.inventory.catalogue.subcategories.quick-create"
    refresh-route="admin.lookups.subcategories"
    permission="catalogue.create"
    :modal-title="__('Create subcategory')"
    option-label-key="label"
    option-value-key="value"
    select-class="erp-input w-full"
/>

<x-admin.lookup-select
    name="brand_id"
    :label="__('Brand')"
    :options="$brands"
    :value="old('brand_id', $m?->brand_id)"
    create-route="admin.inventory.catalogue.brands.quick-create"
    refresh-route="admin.lookups.brands"
    permission="catalogue.create"
    :modal-title="__('Create brand')"
    :placeholder="__('Generic / none')"
    select-class="erp-input w-full"
/>

@if(($fields['unit_of_measure_id']['visible'] ?? true))
<x-admin.lookup-select
    name="unit_of_measure_id"
    :label="__('Unit')"
    :options="$units"
    :value="old('unit_of_measure_id', $m?->unit_of_measure_id)"
    :required="($fields['unit_of_measure_id']['required'] ?? true)"
    :readonly="($fields['unit_of_measure_id']['read_only'] ?? false)"
    create-route="admin.inventory.catalogue.uoms.quick-create"
    refresh-route="admin.lookups.uoms"
    permission="catalogue.create"
    :modal-title="__('Create unit of measure')"
    select-class="erp-input w-full"
    :empty-option="false"
/>
@endif

<div><label class="erp-label">{{ __('Item code') }}</label><input name="item_code" class="erp-input w-full" value="{{ old('item_code', $m?->item_code) }}"></div>

@if(($fields['sku']['visible'] ?? true))
<div><label class="erp-label">{{ __('SKU') }}</label><input name="sku" class="erp-input w-full" value="{{ old('sku', $m?->sku ?? ($fields['sku']['default'] ?? '')) }}" placeholder="{{ __('Leave blank to generate') }}" @readonly($fields['sku']['read_only'] ?? false)></div>
@endif

@if(($fields['item_name']['visible'] ?? true))
<div><label class="erp-label">{{ __('Name') }}</label><input name="item_name" class="erp-input w-full" value="{{ old('item_name', $m?->item_name ?? ($fields['item_name']['default'] ?? '')) }}" @required($fields['item_name']['required'] ?? true) @readonly($fields['item_name']['read_only'] ?? false)></div>
@endif

@if(($fields['description']['visible'] ?? true))
<div><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" @required($fields['description']['required'] ?? false) @readonly($fields['description']['read_only'] ?? false)>{{ old('description', $m?->description ?? ($fields['description']['default'] ?? '')) }}</textarea></div>
@endif

@php($existingAttributes = $m?->attributeValues?->keyBy('item_attribute_id') ?? collect())
@if($attributes->isNotEmpty())
<div class="rounded-lg border border-erp-border p-4">
    <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Product attributes') }}</h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ($attributes as $attribute)
            @php($current = $existingAttributes->get($attribute->id))
            <div>
                <label class="erp-label">{{ $attribute->name }}</label>
                @if ($attribute->data_type === 'select')
                    <select name="attributes[{{ $attribute->id }}]" class="erp-input w-full" @required($attribute->is_required)>
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($attribute->options as $option)
                            <option value="{{ $option->id }}" @selected(old("attributes.{$attribute->id}", $current?->attribute_option_id) == $option->id)>{{ $option->label }}</option>
                        @endforeach
                    </select>
                @else
                    <input name="attributes[{{ $attribute->id }}]" type="{{ $attribute->data_type === 'number' ? 'number' : 'text' }}" class="erp-input w-full" value="{{ old("attributes.{$attribute->id}", $current?->value) }}" @required($attribute->is_required)>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

<div>
    <label class="erp-label">{{ __('Stock role') }}</label>
    <select name="stock_role" class="erp-select w-full" required>
        @foreach ($stockRoles as $role)
            <option value="{{ $role->value }}" @selected(old('stock_role', $m?->stock_role?->value ?? 'raw_material') === $role->value)>{{ $role->label() }}</option>
        @endforeach
    </select>
</div>

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

@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $m ?? null])
