<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
    @foreach ($kpis as $kpi)
        @if ($kpi['clickable'] && ! empty($kpi['url']))
            <a href="{{ $kpi['url'] }}?embedded={{ request('embedded', '') }}" data-turbo-frame="{{ request('embedded') ? 'module-workspace-content' : 'erp-main' }}" class="rounded-md border border-erp-border bg-white px-3 py-2 transition-colors hover:border-erp-primary/40 hover:bg-erp-primary/5">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">{{ $kpi['label'] }}</p>
                <p class="mt-0.5 text-lg font-semibold tabular-nums text-erp-primary">{{ $kpi['value'] }}</p>
            </a>
        @else
            <div class="rounded-md border border-erp-border bg-white px-3 py-2">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">{{ $kpi['label'] }}</p>
                <p class="mt-0.5 text-lg font-semibold tabular-nums text-erp-primary">{{ $kpi['value'] }}</p>
            </div>
        @endif
    @endforeach
</div>
