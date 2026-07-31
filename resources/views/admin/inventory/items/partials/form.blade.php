@php($m = $item ?? null)
@php($fields = $formFields ?? [])
@php($brandDisplay = old('brand_name', $m?->brand_name ?? $m?->brand?->name))

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

@php($subcategoryOptions = $subcategories->map(fn ($s) => ['value' => $s->id, 'label' => $s->name])->values())

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
    scope-category-field="inventory_category_id"
    select-class="erp-input w-full"
/>

<div>
    <label class="erp-label">{{ __('Brand') }}</label>
    <input name="brand_name" class="erp-input w-full" value="{{ $brandDisplay }}" placeholder="{{ __('Enter brand name') }}">
</div>

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

@if(($fields['item_name']['visible'] ?? true))
<div><label class="erp-label">{{ __('Name') }}</label><input name="item_name" class="erp-input w-full" value="{{ old('item_name', $m?->item_name ?? ($fields['item_name']['default'] ?? '')) }}" @required($fields['item_name']['required'] ?? true) @readonly($fields['item_name']['read_only'] ?? false)></div>
@endif

<div>
    <label class="erp-label">{{ __('Stock role') }}</label>
    <select name="stock_role" class="erp-select w-full" required>
        @foreach ($stockRoles as $role)
            <option value="{{ $role->value }}" @selected(old('stock_role', $m?->stock_role?->value ?? 'raw_material') === $role->value)>{{ $role->label() }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-slate-500">{{ __('Finished products must use Finished good. Raw paper/ink use Raw material.') }}</p>
</div>

@if(($fields['description']['visible'] ?? true))
<div><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" @required($fields['description']['required'] ?? false) @readonly($fields['description']['read_only'] ?? false)>{{ old('description', $m?->description ?? ($fields['description']['default'] ?? '')) }}</textarea></div>
@endif

@php($existingAttributes = $m?->attributeValues?->keyBy('item_attribute_id') ?? collect())
@php($attributeCategoryMap = $attributes->mapWithKeys(fn ($attribute) => [$attribute->id => $attribute->inventory_category_id])->all())
@if($attributes->isNotEmpty())
<div
    class="rounded-lg border border-erp-border p-4"
    x-data="{
        categoryId: @js((string) old('inventory_category_id', $m?->inventory_category_id ?? '')),
        attributeCategories: @js($attributeCategoryMap),
        matchesCategory(attributeId) {
            const scoped = this.attributeCategories[attributeId];

            if (! scoped) {
                return true;
            }

            return String(scoped) === String(this.categoryId);
        },
        bindCategoryField() {
            const form = this.$root.closest('form');
            const field = form?.querySelector('[name=\'inventory_category_id\']');

            if (! field) {
                return;
            }

            this.categoryId = field.value ?? '';

            field.addEventListener('change', () => {
                this.categoryId = field.value ?? '';
            });

            form?.addEventListener('erp-lookup-changed', (event) => {
                if (event.detail?.name === 'inventory_category_id') {
                    this.categoryId = event.detail.value ?? '';
                }
            });
        },
    }"
    x-init="bindCategoryField()"
>
    <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Product attributes') }}</h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ($attributes as $attribute)
            @php($current = $existingAttributes->get($attribute->id))
            <div x-show="matchesCategory(@js($attribute->id))" x-cloak>
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

<div class="rounded-lg border border-erp-border p-4" x-data="{ serialEnabled: @js((bool) old('uses_serial_numbers', $m?->uses_serial_numbers ?? false)) }">
    <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Serial numbers') }}</h3>
    <label class="inline-flex items-center gap-2 text-sm mb-4">
        <input type="checkbox" name="uses_serial_numbers" value="1" x-model="serialEnabled" @checked(old('uses_serial_numbers', $m?->uses_serial_numbers ?? false))>
        <span>{{ __('Uses serial numbers') }}</span>
    </label>
    <div x-show="serialEnabled" x-cloak class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="erp-label">{{ __('Serial prefix') }}</label>
            <input name="serial_prefix" class="erp-input w-full" value="{{ old('serial_prefix', $m?->serial_prefix) }}" placeholder="RB-">
            <p class="mt-1 text-xs text-slate-500">{{ __('Example: RB-{NUMBER} → RB-000001') }}</p>
        </div>
        <div>
            <label class="erp-label">{{ __('Padding length') }}</label>
            <input type="number" name="serial_padding_length" class="erp-input w-full" min="1" max="12" value="{{ old('serial_padding_length', $m?->serial_padding_length ?? 6) }}">
        </div>
    </div>
</div>

@php($routeSteps = old('route_steps', $m?->productionRouteSteps?->map(fn ($s) => ['step_name' => $s->step_name, 'sequence' => $s->sequence, 'is_active' => $s->is_active, 'work_center_id' => $s->work_center_id])->values()->all() ?? [['step_name' => '', 'sequence' => 1, 'is_active' => true, 'work_center_id' => null]]))
@php($workCenterOptions = ($workCenters ?? collect())->map(fn ($wc) => ['id' => $wc->id, 'label' => $wc->name])->values())
<div class="rounded-lg border border-erp-border p-4" x-data="{ steps: @js($routeSteps), workCenters: @js($workCenterOptions) }">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('Default production steps') }}</h3>
        <button type="button" class="erp-btn-ghost text-xs" @click="steps.push({ step_name: '', sequence: steps.length + 1, is_active: true, work_center_id: null })">{{ __('Add step') }}</button>
    </div>
    <p class="mb-3 text-xs text-slate-500">{{ __('Route template copied to job cards at creation. Catalog edits do not affect active jobs.') }}</p>
    <template x-for="(step, index) in steps" :key="index">
        <div class="mb-2 grid grid-cols-12 gap-2 items-end">
            <div class="col-span-1">
                <label class="erp-label text-xs">{{ __('Seq') }}</label>
                <input type="number" class="erp-input w-full text-sm" :name="'route_steps[' + index + '][sequence]'" x-model.number="step.sequence" min="1">
            </div>
            <div class="col-span-5">
                <label class="erp-label text-xs">{{ __('Step name') }}</label>
                <input type="text" class="erp-input w-full text-sm" :name="'route_steps[' + index + '][step_name]'" x-model="step.step_name" placeholder="{{ __('e.g. Printing') }}">
            </div>
            <div class="col-span-3">
                <label class="erp-label text-xs">{{ __('Work center') }}</label>
                <select class="erp-input w-full text-sm" :name="'route_steps[' + index + '][work_center_id]'" x-model="step.work_center_id">
                    <option value="">{{ __('—') }}</option>
                    <template x-for="wc in workCenters" :key="wc.id">
                        <option :value="wc.id" x-text="wc.label"></option>
                    </template>
                </select>
            </div>
            <div class="col-span-1">
                <label class="inline-flex items-center gap-1 text-xs">
                    <input type="checkbox" :name="'route_steps[' + index + '][is_active]'" value="1" x-model="step.is_active">
                    {{ __('Active') }}
                </label>
            </div>
            <div class="col-span-2">
                <button type="button" class="erp-btn-ghost text-xs text-red-700" @click="steps.splice(index, 1)">{{ __('Remove') }}</button>
            </div>
        </div>
    </template>
</div>

@php($materialLines = old('material_requirements', ($productBomLines ?? collect())->map(fn ($l) => [
    'inventory_item_id' => $l->inventory_item_id !== null ? (string) $l->inventory_item_id : '',
    'quantity_per_unit' => (float) $l->quantity_per_unit,
    'quantity_formula' => $l->quantity_formula ?? '',
    'is_active' => (bool) ($l->is_active ?? true),
])->values()->all() ?: [['inventory_item_id' => '', 'quantity_per_unit' => 1, 'quantity_formula' => '', 'is_active' => true]]))
<div class="rounded-lg border border-erp-border p-4" x-data="{ materials: @js($materialLines) }">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('Required materials') }}</h3>
        <button type="button" class="erp-btn-ghost text-xs" @click="materials.push({ inventory_item_id: '', quantity_per_unit: 1, quantity_formula: '', is_active: true })">{{ __('Add material') }}</button>
    </div>
    <p class="mb-3 text-xs text-slate-500">{{ __('Defines BOM for finished products. Formula examples: JOB_QTY * 0.01, JOB_QTY / 500, or fixed quantity.') }}</p>
    <template x-for="(line, index) in materials" :key="index">
        <div class="mb-2 grid grid-cols-12 gap-2 items-end">
            <div class="col-span-4">
                <label class="erp-label text-xs">{{ __('Material') }}</label>
                {{-- Blade options (not nested Alpine x-for) so selected BOM materials actually prefill. --}}
                <select
                    class="erp-input w-full text-sm"
                    :name="'material_requirements[' + index + '][inventory_item_id]'"
                    x-model="line.inventory_item_id"
                >
                    <option value="">{{ __('Select') }}</option>
                    @foreach ($rawMaterials ?? [] as $rm)
                        <option value="{{ $rm->id }}">{{ $rm->sku }} — {{ $rm->item_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="erp-label text-xs">{{ __('Per unit') }}</label>
                <input type="number" step="0.0001" class="erp-input w-full text-sm" :name="'material_requirements[' + index + '][quantity_per_unit]'" x-model.number="line.quantity_per_unit" min="0.0001">
            </div>
            <div class="col-span-3">
                <label class="erp-label text-xs">{{ __('Formula') }}</label>
                <input type="text" class="erp-input w-full text-sm" :name="'material_requirements[' + index + '][quantity_formula]'" x-model="line.quantity_formula" placeholder="JOB_QTY * 0.01">
            </div>
            <div class="col-span-1">
                <label class="inline-flex items-center gap-1 text-xs">
                    <input type="checkbox" :name="'material_requirements[' + index + '][is_active]'" value="1" x-model="line.is_active">
                    {{ __('Active') }}
                </label>
            </div>
            <div class="col-span-2">
                <button type="button" class="erp-btn-ghost text-xs text-red-700" @click="materials.splice(index, 1)">{{ __('Remove') }}</button>
            </div>
        </div>
    </template>
</div>

@php($qcLines = old('qc_checklist', ($productQcChecklistLines ?? collect())->map(fn ($l) => [
    'label' => $l->label,
    'is_active' => $l->is_active ?? true,
])->values()->all() ?: app(\App\Support\Production\ProductQcChecklistService::class)->defaultLinePayload()->all()))
<div class="rounded-lg border border-erp-border p-4" x-data="{ qcItems: @js($qcLines) }">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('QC checklist') }}</h3>
        <button type="button" class="erp-btn-ghost text-xs" @click="qcItems.push({ label: '', is_active: true })">{{ __('Add item') }}</button>
    </div>
    <p class="mb-3 text-xs text-slate-500">{{ __('Inspection checklist snapshotted to job cards when sent to QC.') }}</p>
    <label class="mb-3 inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="requires_customer_approval" value="1" @checked(old('requires_customer_approval', $m?->requires_customer_approval ?? false))>
        {{ __('Requires customer approval (large branding / signage / custom design)') }}
    </label>
    <template x-for="(item, index) in qcItems" :key="index">
        <div class="mb-2 grid grid-cols-12 gap-2 items-end">
            <div class="col-span-8">
                <label class="erp-label text-xs">{{ __('Check item') }}</label>
                <input type="text" class="erp-input w-full text-sm" :name="'qc_checklist[' + index + '][label]'" x-model="item.label" required>
            </div>
            <div class="col-span-2">
                <label class="inline-flex items-center gap-1 text-xs">
                    <input type="checkbox" :name="'qc_checklist[' + index + '][is_active]'" value="1" x-model="item.is_active">
                    {{ __('Active') }}
                </label>
            </div>
            <div class="col-span-2">
                <button type="button" class="erp-btn-ghost text-xs text-red-700" @click="qcItems.splice(index, 1)">{{ __('Remove') }}</button>
            </div>
        </div>
    </template>
</div>
