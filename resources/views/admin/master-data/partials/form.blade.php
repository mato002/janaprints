@props(['value' => null, 'categories' => [], 'selectedCategory' => null, 'showCategory' => true])

<div class="space-y-4">
    @if ($showCategory)
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Category') }}</label>
            <select name="category_key" class="erp-input mt-1 w-full" required @disabled($value)>
                <option value="">{{ __('Select category') }}</option>
                @foreach ($categories as $option)
                    <option value="{{ $option['value'] }}" @selected(old('category_key', $value?->category_key ?? $selectedCategory) === $option['value'])>
                        {{ $option['module'] }} · {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
        @unless ($value)
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Code') }}</label>
                <input type="text" name="code" value="{{ old('code') }}" class="erp-input mt-1 w-full" placeholder="{{ __('Auto-generated') }}" maxlength="80" />
            </div>
        @endunless
    @endif

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Name') }}</label>
        <input type="text" name="name" value="{{ old('name', $value?->name) }}" class="erp-input mt-1 w-full" required maxlength="255" />
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Description') }}</label>
        <textarea name="description" rows="3" class="erp-input mt-1 w-full">{{ old('description', $value?->description) }}</textarea>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sort order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $value?->sort_order ?? 0) }}" class="erp-input mt-1 w-full" min="0" max="9999" />
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="hidden" name="is_active" value="0" />
                <input type="checkbox" name="is_active" value="1" class="rounded border-erp-border text-erp-accent" @checked(old('is_active', $value?->is_active ?? true)) />
                {{ __('Active') }}
            </label>
        </div>
    </div>
</div>
