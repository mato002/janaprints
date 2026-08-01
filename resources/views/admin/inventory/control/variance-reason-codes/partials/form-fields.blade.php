@php($m = $code ?? null)
<div><x-admin.entity-code-input :record="$m" erp maxlength="50" /></div>
<div><label class="erp-label">{{ __('Name') }}</label><input name="name" class="erp-input w-full" value="{{ old('name', $m?->name) }}" required maxlength="255"></div>
<div>
    <label class="erp-label">{{ __('Category') }}</label>
    <select name="category" class="erp-select w-full" required>
        @foreach ($categories as $category)
            <option value="{{ $category->value }}" @selected(old('category', $m?->category?->value) === $category->value)>{{ $category->label() }}</option>
        @endforeach
    </select>
</div>
<label class="inline-flex items-center gap-2 text-sm">
    <input type="hidden" name="requires_comment" value="0">
    <input type="checkbox" name="requires_comment" value="1" @checked(old('requires_comment', $m?->requires_comment ?? true))>
    <span>{{ __('Comment required when used') }}</span>
</label>
<label class="inline-flex items-center gap-2 text-sm">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m?->is_active ?? true))>
    <span>{{ __('Active') }}</span>
</label>
