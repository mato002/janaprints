@props(['tabs', 'active_tab', 'filters'])

<div class="mb-4 flex flex-wrap gap-2 border-b border-erp-border pb-3">
    @foreach ($tabs as $tab)
        <a
            href="{{ route('admin.reports.hr', array_merge($filters, ['tab' => $tab['key']])) }}"
            @class([
                'rounded-md px-3 py-1.5 text-sm font-medium transition',
                'bg-erp-primary text-white' => $active_tab === $tab['key'],
                'text-slate-600 hover:bg-slate-100' => $active_tab !== $tab['key'],
            ])
            data-turbo-frame="erp-main"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
