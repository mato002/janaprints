@php($m = $brand ?? null)
<div class="erp-form-grid">
    <div><label class="erp-label">{{ __('Name') }}</label><input name="name" class="erp-input w-full" value="{{ old('name', $m?->name) }}" required></div>
    <x-admin.entity-code-input :record="$m" erp />
    <div class="md:col-span-2"><label class="erp-label">{{ __('Logo') }}</label><input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp" class="erp-input w-full"></div>
    <div class="md:col-span-2"><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" rows="3">{{ old('description', $m?->description) }}</textarea></div>
    <div><input type="hidden" name="is_active" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m?->is_active ?? true))><span>{{ __('Active') }}</span></label></div>
</div>
