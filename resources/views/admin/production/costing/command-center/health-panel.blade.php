<x-admin.collapsible-summary :title="__('Profitability health')">
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    @foreach ($dashboard['health'] as $item)
        <a
            href="{{ $item['filter_url'] }}"
            class="rounded-xl border border-erp-border bg-white p-4 transition-colors hover:border-erp-accent hover:bg-slate-50"
            data-turbo-frame="erp-main"
        >
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                <x-admin.status-badge :variant="$item['variant']">{{ $item['badge'] }}</x-admin.status-badge>
            </div>
            <p class="mt-2 text-2xl font-bold tabular-nums text-erp-primary">{{ $item['count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $item['description'] }}</p>
        </a>
    @endforeach
    </div>
</x-admin.collapsible-summary>
