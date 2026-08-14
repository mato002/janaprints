@props(['kpis'])

<section class="mb-6">
    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Supplier Intelligence Dashboard') }}</h2>
    <div class="erp-kpi-grid">
        @foreach ($kpis as $kpi)
            <x-admin.kpi-widget
                :label="$kpi['label']"
                :value="$kpi['value']"
                :icon="$kpi['icon'] ?? 'chart-bar'"
                :hint="$kpi['hint'] ?? null"
            />
        @endforeach
    </div>
</section>
