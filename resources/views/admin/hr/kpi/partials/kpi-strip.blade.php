@props(['kpis'])

<x-admin.card class="mb-6">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        @foreach ($kpis as $kpi)
            <div class="rounded-lg border border-erp-border/70 p-4">
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ $kpi['label'] }}</p>
                <p class="mt-1 text-xl font-bold tabular-nums text-erp-primary">{{ $kpi['value'] }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $kpi['hint'] }}</p>
                <span @class([
                    'mt-2 inline-block rounded px-2 py-0.5 text-[10px] font-semibold uppercase',
                    'bg-emerald-50 text-emerald-700' => $kpi['status'] === 'good',
                    'bg-amber-50 text-amber-700' => $kpi['status'] === 'watch',
                    'bg-red-50 text-red-700' => $kpi['status'] === 'critical',
                    'bg-slate-100 text-slate-600' => $kpi['status'] === 'pending',
                ])>{{ ucfirst($kpi['status']) }}</span>
            </div>
        @endforeach
    </div>
</x-admin.card>
