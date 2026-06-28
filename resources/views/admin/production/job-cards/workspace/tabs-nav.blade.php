@php
    $groups = $workspace['tab_groups'] ?? ['primary' => $tabs, 'more' => [], 'more_open' => false];
@endphp

<nav class="c360-tabs" aria-label="{{ __('Job workspace tabs') }}">
    @foreach ($groups['primary'] as $tab)
        <a
            href="{{ $tab['url'] }}"
            class="c360-tabs__link {{ $tab['active'] ? 'c360-tabs__link--active' : '' }}"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            @if ($tab['active']) aria-current="page" @endif
        >{{ $tab['label'] }}</a>
    @endforeach

    @if (! empty($groups['more']))
        <details class="c360-tabs__more inline-block" @if ($groups['more_open'] ?? false) open @endif>
            <summary class="c360-tabs__link cursor-pointer list-none {{ collect($groups['more'])->contains('active', true) ? 'c360-tabs__link--active' : '' }}">
                {{ __('More') }}
            </summary>
            <div class="mt-1 flex flex-wrap gap-1 rounded-lg border border-erp-border bg-white p-2 shadow-sm">
                @foreach ($groups['more'] as $tab)
                    <a
                        href="{{ $tab['url'] }}"
                        class="rounded px-2 py-1 text-xs {{ $tab['active'] ? 'bg-erp-accent/10 font-semibold text-erp-accent' : 'text-slate-600 hover:bg-slate-50' }}"
                        data-turbo-frame="erp-main"
                        data-turbo-action="advance"
                    >{{ $tab['label'] }}</a>
                @endforeach
            </div>
        </details>
    @endif
</nav>
