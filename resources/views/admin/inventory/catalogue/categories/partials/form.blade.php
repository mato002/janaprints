@php($m = $category ?? null)
<div class="erp-form-grid">
    <div><label class="erp-label">{{ __('Name') }}</label><input name="name" class="erp-input w-full" value="{{ old('name', $m?->name) }}" required></div>
    <x-admin.entity-code-input :record="$m" erp />
    <div>
        <label class="erp-label">{{ __('Default UOM') }}</label>
        <select name="default_uom_id" class="erp-select w-full">
            <option value="">{{ __('None') }}</option>
            @foreach ($units as $unit)<option value="{{ $unit->id }}" @selected(old('default_uom_id', $m?->default_uom_id) == $unit->id)>{{ $unit->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="erp-label">{{ __('Reorder behavior') }}</label>
        <select name="reorder_behavior" class="erp-select w-full" required>
            @foreach (['standard' => 'Standard', 'made_to_order' => 'Made to order', 'non_stock' => 'Non-stock', 'critical' => 'Critical'] as $value => $label)
                <option value="{{ $value }}" @selected(old('reorder_behavior', $m?->reorder_behavior ?? 'standard') === $value)>{{ __($label) }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2"><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" rows="3">{{ old('description', $m?->description) }}</textarea></div>
    <div class="md:col-span-2"><input type="hidden" name="is_active" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m?->is_active ?? true))><span>{{ __('Active') }}</span></label></div>
</div>
