@props(['tabs', 'active_tab', 'filters', 'turbo_frame' => 'erp-main'])

<x-admin.card class="mb-4 !p-2">
    <nav class="flex flex-wrap gap-1" role="tablist" aria-label="{{ __('Production report categories') }}">
        @foreach ($tabs as $tab)
            @php
                $query = array_merge($filters, ['tab' => $tab['key']]);
                if (request('embedded')) {
                    $query['embedded'] = '1';
                }
            @endphp
            <a
                href="{{ route('admin.reports.production', $query) }}"
                role="tab"
                @class([
                    'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    'bg-erp-primary text-white shadow-sm' => $active_tab === $tab['key'],
                    'text-slate-600 hover:bg-erp-page hover:text-erp-primary' => $active_tab !== $tab['key'],
                ])
                @if ($active_tab === $tab['key']) aria-selected="true" @else aria-selected="false" @endif
                data-turbo-frame="{{ $turbo_frame }}"
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</x-admin.card>
