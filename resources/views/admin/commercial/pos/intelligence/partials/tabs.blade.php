@props(['tabs', 'active_tab', 'filters'])

<nav class="mb-4 flex flex-wrap gap-2 border-b border-erp-border pb-3">
    @foreach ($tabs as $tab)
        <a
            href="{{ route('commercial.pos.reports.index', array_merge($filters, ['tab' => $tab['key'], 'page' => 1])) }}"
            data-turbo-frame="erp-main"
            @class([
                'rounded-md px-3 py-1.5 text-sm font-medium transition',
                'bg-erp-accent text-white' => $active_tab === $tab['key'],
                'text-slate-600 hover:bg-slate-100' => $active_tab !== $tab['key'],
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
