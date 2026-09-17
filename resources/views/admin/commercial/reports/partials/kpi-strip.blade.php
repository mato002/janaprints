@props(['kpis'])

<x-admin.collapsible-summary>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach ($kpis as $kpi)
            <x-admin.kpi-widget
                :label="$kpi['label']"
                :value="$kpi['value']"
                :icon="$kpi['icon'] ?? 'chart-bar'"
                :hint="$kpi['hint'] ?? null"
            />
        @endforeach
    </div>
</x-admin.collapsible-summary>
