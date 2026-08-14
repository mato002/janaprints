@props(['title', 'widgets'])

<section class="mb-6">
    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $title }}</h2>
    <div class="erp-kpi-grid">
        @foreach ($widgets as $widget)
            <x-admin.kpi-widget
                :label="$widget['label']"
                :value="$widget['value']"
                :icon="$widget['icon'] ?? 'chart-pie'"
                :hint="$widget['hint'] ?? null"
            />
        @endforeach
    </div>
</section>
