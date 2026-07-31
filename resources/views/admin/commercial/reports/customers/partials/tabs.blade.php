@props(['tabs', 'active_tab', 'filters', 'index_route' => null])

@php
    $indexRoute = $index_route ?? 'admin.commercial.reports.customers.index';
@endphp

<div class="mb-4 overflow-x-auto">
    <nav class="flex gap-1 border-b border-erp-border" aria-label="{{ __('Customer report tabs') }}">
        @foreach ($tabs as $tab)
            @php
                $query = array_merge($filters, ['tab' => $tab['key'], 'page' => 1]);
            @endphp
            <a
                href="{{ \App\Support\Navigation\WorkspaceEmbed::url(route($indexRoute, $query)) }}"
                data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}" data-turbo-action="advance"
                @class([
                    'whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium transition-colors',
                    'border-erp-accent text-erp-accent' => $active_tab === $tab['key'],
                    'border-transparent text-slate-500 hover:border-slate-300 hover:text-erp-primary' => $active_tab !== $tab['key'],
                ])
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</div>
