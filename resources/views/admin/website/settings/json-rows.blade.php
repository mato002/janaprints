@php
    $field = str_replace('.', '_', $key);
    $rows = old($field);

    if ($rows === null) {
        $rows = is_array($resolved) ? $resolved : [];
    } elseif (is_string($rows)) {
        $rows = json_decode($rows, true, 512, JSON_THROW_ON_ERROR) ?? [];
    }

    $isNav = $key === 'footer.nav';
    $isBadges = $key === 'footer.trust_badges';
@endphp

<div class="lg:col-span-2" data-json-rows-editor data-json-field="{{ $field }}" data-settings-field data-settings-label="{{ strtolower($meta['label']) }}" data-settings-key="{{ strtolower($key) }}">
    <label class="erp-label">{{ $meta['label'] }}</label>
    @if (! empty($meta['description']))
        <p class="mb-2 text-xs text-slate-500">{{ $meta['description'] }}</p>
    @endif

    <div class="space-y-2" data-json-rows-list data-empty-label="{{ __('No rows yet. Add one below.') }}">
        @forelse ($rows as $index => $row)
            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3" data-json-row>
                @if ($isNav)
                    <input
                        type="text"
                        name="{{ $field }}[{{ $index }}][label]"
                        value="{{ is_array($row) ? ($row['label'] ?? '') : '' }}"
                        class="erp-input min-w-[8rem] flex-1"
                        placeholder="{{ __('Link label') }}"
                        required
                    >
                    <input
                        type="text"
                        name="{{ $field }}[{{ $index }}][href]"
                        value="{{ is_array($row) ? ($row['href'] ?? '') : '' }}"
                        class="erp-input min-w-[12rem] flex-[2]"
                        placeholder="{{ __('URL or path, e.g. /services') }}"
                        required
                    >
                @elseif ($isBadges)
                    <input
                        type="text"
                        name="{{ $field }}[{{ $index }}]"
                        value="{{ is_string($row) ? $row : '' }}"
                        class="erp-input flex-1"
                        placeholder="{{ __('Badge label') }}"
                        required
                    >
                @endif
                <button type="button" class="erp-btn-secondary text-xs" data-json-row-remove>{{ __('Remove') }}</button>
            </div>
        @empty
            <p class="text-xs text-slate-500" data-json-rows-empty>{{ __('No rows yet. Add one below.') }}</p>
        @endforelse
    </div>

    <button type="button" class="erp-btn-secondary mt-3 text-xs" data-json-row-add>
        {{ $isNav ? __('Add navigation link') : __('Add badge') }}
    </button>

    @if ($isNav)
        <p class="mt-2 text-xs text-slate-500">{{ __('Example:') }} <code>{"label":"Services","href":"/services"}</code></p>
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
