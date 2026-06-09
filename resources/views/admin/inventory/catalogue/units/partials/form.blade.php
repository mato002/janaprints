@php($m = $unit ?? null)
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="erp-label">{{ __('Code') }}</label>
        <input type="text" name="code" class="erp-input w-full" value="{{ old('code', $m?->code) }}" required maxlength="50">
    </div>
    <div>
        <label class="erp-label">{{ __('Name') }}</label>
        <input type="text" name="name" class="erp-input w-full" value="{{ old('name', $m?->name) }}" required maxlength="255">
    </div>
    <div>
        <label class="erp-label">{{ __('Base unit') }}</label>
        <select name="base_unit_id" class="erp-select w-full">
            <option value="">{{ __('This is a base unit') }}</option>
            @foreach ($baseUnits as $baseUnit)
                <option value="{{ $baseUnit->id }}" @selected((string) old('base_unit_id', $m?->base_unit_id) === (string) $baseUnit->id)>{{ $baseUnit->name }} ({{ $baseUnit->code }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label">{{ __('Conversion factor') }}</label>
        <input type="number" step="0.0001" min="0.0001" name="conversion_factor" class="erp-input w-full" value="{{ old('conversion_factor', $m?->conversion_factor ?? 1) }}">
        <p class="mt-1 text-xs text-slate-500">{{ __('Example: 1 Ream = 500 Sheets') }}</p>
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $m?->is_active ?? true))>
            {{ __('Active') }}
        </label>
    </div>
</div>
