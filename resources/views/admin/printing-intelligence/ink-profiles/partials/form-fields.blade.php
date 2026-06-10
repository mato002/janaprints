@props([
    'profile' => null,
    'inkTypes' => [],
    'inventoryItems' => [],
])

@php
    $isEdit = $profile !== null;
    $prefix = $isEdit ? 'edit_'.$profile['id'] : 'create';
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="{{ $prefix }}_name">{{ __('Name') }}</label>
        <input id="{{ $prefix }}_name" type="text" name="name" value="{{ old('name', $profile['name'] ?? '') }}" class="erp-input mt-1 w-full text-sm" required />
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="{{ $prefix }}_ink_type">{{ __('Ink Type') }}</label>
        <select id="{{ $prefix }}_ink_type" name="ink_type" class="erp-select mt-1 w-full text-sm" required>
            <option value="">{{ __('Select ink type') }}</option>
            @foreach ($inkTypes as $type)
                <option value="{{ $type->value }}" @selected(old('ink_type', $profile['ink_type_value'] ?? '') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('ink_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="{{ $prefix }}_inventory_item_id">{{ __('Inventory Item') }}</label>
        <select id="{{ $prefix }}_inventory_item_id" name="inventory_item_id" class="erp-select mt-1 w-full text-sm">
            <option value="">{{ __('None') }}</option>
            @foreach ($inventoryItems as $item)
                <option value="{{ $item['id'] }}" @selected((string) old('inventory_item_id', $profile['inventory_item_id'] ?? '') === (string) $item['id'])>{{ $item['label'] }}</option>
            @endforeach
        </select>
        @error('inventory_item_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="{{ $prefix }}_cartridge_cost">{{ __('Cartridge Cost') }}</label>
        <input id="{{ $prefix }}_cartridge_cost" type="number" step="0.01" min="0" name="cartridge_cost" value="{{ old('cartridge_cost', $profile['cartridge_cost'] ?? '') }}" class="erp-input mt-1 w-full text-sm" required />
        @error('cartridge_cost')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="{{ $prefix }}_estimated_ml">{{ __('Estimated ml') }}</label>
        <input id="{{ $prefix }}_estimated_ml" type="number" step="0.001" min="0" name="estimated_ml" value="{{ old('estimated_ml', $profile['estimated_ml'] ?? '') }}" class="erp-input mt-1 w-full text-sm" />
        @error('estimated_ml')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="{{ $prefix }}_cost_per_ml">{{ __('Cost/ml (override)') }}</label>
        <input id="{{ $prefix }}_cost_per_ml" type="number" step="0.0001" min="0" name="cost_per_ml" value="{{ old('cost_per_ml', $profile['cost_per_ml_override'] ?? '') }}" class="erp-input mt-1 w-full text-sm" />
        <p class="mt-1 text-[11px] text-slate-500">{{ __('Leave blank to derive from cartridge cost ÷ estimated ml.') }}</p>
        @error('cost_per_ml')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="{{ $prefix }}_estimated_yield_pages">{{ __('Yield Pages') }}</label>
        <input id="{{ $prefix }}_estimated_yield_pages" type="number" min="0" name="estimated_yield_pages" value="{{ old('estimated_yield_pages', $profile['estimated_yield_pages'] ?? '') }}" class="erp-input mt-1 w-full text-sm" />
        @error('estimated_yield_pages')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="{{ $prefix }}_estimated_yield_sq_m">{{ __('Yield m²') }}</label>
        <input id="{{ $prefix }}_estimated_yield_sq_m" type="number" step="0.001" min="0" name="estimated_yield_sq_m" value="{{ old('estimated_yield_sq_m', $profile['estimated_yield_sq_m'] ?? '') }}" class="erp-input mt-1 w-full text-sm" />
        @error('estimated_yield_sq_m')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2 flex items-center gap-2">
        <input type="hidden" name="active" value="0">
        <input id="{{ $prefix }}_active" type="checkbox" name="active" value="1" class="rounded border-slate-300" @checked(old('active', $profile['active'] ?? true)) />
        <label for="{{ $prefix }}_active" class="text-sm text-slate-700">{{ __('Active') }}</label>
    </div>
</div>
