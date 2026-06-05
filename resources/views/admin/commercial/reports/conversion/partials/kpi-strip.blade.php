@props(['kpis'])

<section class="mb-6">
    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Conversion Dashboard') }}</h2>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
        @foreach ($kpis as $kpi)
            <x-admin.kpi-widget
                :label="$kpi['label']"
                :value="$kpi['value']"
                :icon="$kpi['icon'] ?? 'chart-pie'"
                :hint="$kpi['hint'] ?? null"
            />
        @endforeach
    </div>
</section>
