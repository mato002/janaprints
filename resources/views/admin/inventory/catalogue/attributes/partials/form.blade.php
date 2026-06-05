@php($m = $attribute ?? null)
<div class="erp-form-grid">
    <div><label class="erp-label">{{ __('Category') }}</label><select name="inventory_category_id" class="erp-select w-full"><option value="">{{ __('Reusable across categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('inventory_category_id', $m?->inventory_category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
    <div><label class="erp-label">{{ __('Code') }}</label><input name="code" class="erp-input w-full" value="{{ old('code', $m?->code) }}" required></div>
    <div><label class="erp-label">{{ __('Name') }}</label><input name="name" class="erp-input w-full" value="{{ old('name', $m?->name) }}" required></div>
    <div><label class="erp-label">{{ __('Data type') }}</label><select name="data_type" class="erp-select w-full" required>@foreach (['text' => 'Text', 'number' => 'Number', 'select' => 'Select'] as $value => $label)<option value="{{ $value }}" @selected(old('data_type', $m?->data_type ?? 'text') === $value)>{{ __($label) }}</option>@endforeach</select></div>
    <div class="md:col-span-2"><label class="erp-label">{{ __('Options for select attributes') }}</label><textarea name="options" class="erp-input w-full" rows="4" placeholder="A4&#10;A3&#10;130 GSM">{{ old('options', isset($m) ? $m->options->pluck('label')->join(PHP_EOL) : '') }}</textarea></div>
    <div><input type="hidden" name="is_required" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_required" value="1" @checked(old('is_required', $m?->is_required ?? false))><span>{{ __('Required') }}</span></label></div>
    <div><input type="hidden" name="is_active" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m?->is_active ?? true))><span>{{ __('Active') }}</span></label></div>
</div>
