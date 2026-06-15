@php
    $field = str_replace('.', '_', $key);
    $resolved = $resolver->get($key, null, $companyId);
    $displayValue = old($field, $resolved);
@endphp

<div
    @class(['lg:col-span-2' => in_array($meta['type'], ['text'], true)])
    data-settings-field
    data-settings-label="{{ strtolower($meta['label']) }}"
    data-settings-key="{{ strtolower($key) }}"
>
    <label class="erp-label" for="{{ $field }}">{{ $meta['label'] }}</label>
    @if (! empty($meta['description']))
        <p class="mb-1 text-xs text-slate-500">{{ $meta['description'] }}</p>
    @endif

    @if ($record?->fallback_value && $resolved !== null && (string) $record?->value !== (string) $record?->fallback_value)
        <p class="mb-2 text-xs text-slate-500">
            {{ __('Fallback:') }}
            <span class="font-mono">{{ Str::limit((string) ($record?->fallback_value ?? $resolved), 80) }}</span>
        </p>
    @endif

    @if ($meta['type'] === 'text')
        <textarea id="{{ $field }}" name="{{ $field }}" rows="4" class="erp-input">{{ $displayValue }}</textarea>
    @else
        <input
            id="{{ $field }}"
            name="{{ $field }}"
            type="{{ in_array($meta['type'], ['email'], true) ? $meta['type'] : 'text' }}"
            class="erp-input"
            value="{{ $displayValue }}"
        >
    @endif

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <p class="font-mono text-xs text-slate-400">{{ $key }}</p>
        @if ($record?->fallback_value)
            <span class="text-xs text-slate-500">{{ __('Config fallback preserved in database.') }}</span>
        @endif
        @can('update', App\Models\DocumentSetting::class)
            @if ($record?->value)
                <button
                    type="button"
                    class="text-xs text-slate-500 underline hover:text-slate-700"
                    data-document-settings-reset-trigger
                    data-reset-url="{{ route('admin.documents.settings.reset', $key) }}"
                    data-reset-confirm="{{ __('Reset this setting to the config fallback?') }}"
                >
                    {{ __('Reset to fallback') }}
                </button>
            @endif
        @endcan
    </div>

    @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
