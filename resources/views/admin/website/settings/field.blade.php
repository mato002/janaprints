@php
    $field = str_replace('.', '_', $key);
    $resolved = $resolver->get($key);
    $displayValue = old($field);

    if ($displayValue === null) {
        if ($meta['type'] === 'json') {
            $displayValue = json_encode($resolved ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif ($meta['type'] === 'boolean') {
            $displayValue = (bool) $resolved;
        } else {
            $displayValue = $resolved;

            if (
                ($meta['optional'] ?? false)
                && ($meta['type'] ?? '') === 'url'
                && (! is_string($displayValue) || $displayValue === '#' || ! filter_var($displayValue, FILTER_VALIDATE_URL))
            ) {
                $displayValue = '';
            }
        }
    }
@endphp

@if ($key === 'footer.social')
    @can('update', App\Models\WebsiteSetting::class)
        <details class="lg:col-span-2 rounded-lg border border-slate-200 bg-slate-50 p-4" data-settings-field data-settings-label="advanced social json" data-settings-key="footer.social">
            <summary class="cursor-pointer text-sm font-medium text-slate-700">{{ __('Advanced Settings (JSON)') }}</summary>
            <p class="mt-2 text-xs text-slate-500">{{ __('Use the Social Links tab for Facebook, Instagram, LinkedIn, and Twitter/X. Only edit this JSON if you need advanced control.') }}</p>
            <textarea id="{{ $field }}" name="{{ $field }}" rows="6" class="erp-input mt-3 font-mono text-xs">{{ $displayValue }}</textarea>
            @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </details>
    @endcan
@elseif ($meta['type'] === 'json' && in_array($key, ['footer.nav', 'footer.trust_badges'], true))
    @include('admin.website.settings.json-rows', [
        'key' => $key,
        'meta' => $meta,
        'record' => $record,
        'resolved' => $resolved,
    ])
@else
<div @class(['lg:col-span-2' => in_array($meta['type'], ['json'], true), 'settings-field' => true]) data-settings-field data-settings-label="{{ strtolower($meta['label']) }}" data-settings-key="{{ strtolower($key) }}">
    <label class="erp-label" for="{{ $field }}">{{ $meta['label'] }}</label>
    @if (! empty($meta['description']))
        <p class="mb-1 text-xs text-slate-500">{{ $meta['description'] }}</p>
    @endif

    @if (($record?->value || $record?->fallback_value) && $resolved !== null && (string) $record?->value !== (string) $record?->fallback_value)
        <p class="mb-2 text-xs text-slate-500">
            {{ __('Fallback:') }}
            <span class="font-mono">{{ is_array($record?->fallback_value ? json_decode((string) $record->fallback_value, true) : null) ? __('JSON structure from config') : Str::limit((string) ($record?->fallback_value ?? $resolved), 80) }}</span>
        </p>
    @endif

    @if ($meta['type'] === 'json')
        <textarea id="{{ $field }}" name="{{ $field }}" rows="6" class="erp-input font-mono text-xs" required>{{ $displayValue }}</textarea>
        <p class="mt-1 text-xs text-slate-500">{{ __('Must be valid JSON. Example for social links:') }} <code>[{"label":"Instagram","href":"https://instagram.com/brand","icon":"instagram"}]</code></p>
    @elseif ($meta['type'] === 'boolean')
        <input type="hidden" name="{{ $field }}" value="0">
        <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
            <input
                id="{{ $field }}"
                name="{{ $field }}"
                type="checkbox"
                value="1"
                class="rounded border-slate-300 text-brand-magenta focus:ring-brand-magenta"
                @checked((bool) $displayValue)
            >
            <span>{{ __('Enabled') }}</span>
        </label>
    @else
        <input
            id="{{ $field }}"
            name="{{ $field }}"
            type="{{ in_array($meta['type'], ['email', 'url']) ? $meta['type'] : 'text' }}"
            class="erp-input"
            value="{{ $meta['type'] === 'json' ? '' : $displayValue }}"
            @if (! ($meta['optional'] ?? false)) required @endif
        >
    @endif

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <p class="font-mono text-xs text-slate-400">{{ $key }}</p>
        @if ($record?->fallback_value)
            <span class="text-xs text-slate-500">{{ __('Config fallback preserved in database.') }}</span>
        @endif
        @can('update', App\Models\WebsiteSetting::class)
            @if ($record?->value)
                <button
                    type="button"
                    class="text-xs text-slate-500 underline hover:text-slate-700"
                    data-website-settings-reset-trigger
                    data-reset-url="{{ route('admin.website.settings.reset', $key) }}"
                    data-reset-confirm="{{ __('Reset this setting to the config fallback?') }}"
                >
                    {{ __('Reset to fallback') }}
                </button>
            @endif
        @endcan
    </div>

    @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
@endif
