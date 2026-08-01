@php($wc = $workCenter ?? null)
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="erp-label" for="name">{{ __('Name') }}</label>
        <input id="name" name="name" class="erp-input w-full" value="{{ old('name', $wc?->name) }}" required>
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-admin.entity-code-input :record="$wc" erp maxlength="30" />
        @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="description">{{ __('Description') }}</label>
        <textarea id="description" name="description" class="erp-input w-full" rows="3">{{ old('description', $wc?->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $wc?->is_active ?? true))>
            <span>{{ __('Active') }}</span>
        </label>
    </div>
    <div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="requires_machine" value="1" @checked(old('requires_machine', $wc?->requires_machine ?? false))>
            <span>{{ __('Requires machine before Start work') }}</span>
        </label>
        <p class="mt-1 text-xs text-slate-500">{{ __('Leave off for design, prepress, packing, or other stages that only need an operator.') }}</p>
    </div>
</div>
