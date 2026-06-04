@php($m = $subcategory ?? null)
<div class="erp-form-grid">
    <div><label class="erp-label">{{ __('Category') }}</label><select name="inventory_category_id" class="erp-select w-full" required>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('inventory_category_id', $m?->inventory_category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
    <div><label class="erp-label">{{ __('Code') }}</label><input name="code" class="erp-input w-full" value="{{ old('code', $m?->code) }}" required></div>
    <div><label class="erp-label">{{ __('Name') }}</label><input name="name" class="erp-input w-full" value="{{ old('name', $m?->name) }}" required></div>
    <div class="md:col-span-2"><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" rows="3">{{ old('description', $m?->description) }}</textarea></div>
    <div><input type="hidden" name="is_active" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m?->is_active ?? true))><span>{{ __('Active') }}</span></label></div>
</div>
