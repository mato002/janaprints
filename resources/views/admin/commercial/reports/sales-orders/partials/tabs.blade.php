@props(['tabs', 'active_tab', 'filters', 'index_route' => null])

@php
    $indexRoute = $index_route ?? 'admin.commercial.reports.sales_orders.index';
@endphp

<nav class="mb-4 flex flex-wrap gap-1 border-b border-erp-border" aria-label="{{ __('Report tabs') }}">
    @foreach ($tabs as $tab)
        @php
            $query = array_merge($filters, ['tab' => $tab['key'], 'page' => 1]);
        @endphp
        <a
            href="{{ \App\Support\Navigation\WorkspaceEmbed::url(route($indexRoute, $query)) }}"
            data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}" data-turbo-action="advance"
            @class([
                'inline-flex items-center border-b-2 px-3 py-2 text-xs font-semibold transition',
                'border-erp-accent text-erp-accent' => $active_tab === $tab['key'],
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-erp-primary' => $active_tab !== $tab['key'],
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
