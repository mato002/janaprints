@props(['tabs', 'active_tab', 'filters'])

<nav class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-white p-1 shadow-sm" aria-label="{{ __('Artwork report tabs') }}">
    @foreach ($tabs as $tab)
        <a
            href="{{ route('commercial.reports.artwork.index', array_merge($filters, ['tab' => $tab['key'], 'page' => 1])) }}"
            class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold transition-colors {{ $active_tab === $tab['key'] ? 'bg-erp-accent text-white shadow-sm' : 'text-slate-700 hover:bg-slate-50' }}"
            data-turbo-frame="erp-main"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
