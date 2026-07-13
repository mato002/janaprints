@props(['tabs', 'active_tab', 'filters'])

<x-admin.card class="mb-6 !p-2">
    <nav class="flex flex-wrap gap-1" role="tablist" aria-label="{{ __('Quotation report tabs') }}">
        @foreach ($tabs as $tab)
            @php $query = array_merge($filters, ['tab' => $tab['key'], 'page' => 1]); @endphp
            <a
                href="{{ \App\Support\Navigation\WorkspaceEmbed::url(route('admin.commercial.reports.quotations.index', $query)) }}"
                role="tab"
                @class([
                    'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    'bg-erp-primary text-white shadow-sm' => $active_tab === $tab['key'],
                    'text-slate-600 hover:bg-erp-page hover:text-erp-primary' => $active_tab !== $tab['key'],
                ])
                @if ($active_tab === $tab['key']) aria-selected="true" @else aria-selected="false" @endif
                data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}" data-turbo-action="advance"
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</x-admin.card>
