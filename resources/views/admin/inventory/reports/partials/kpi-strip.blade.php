@props(['kpis'])

<x-admin.collapsible-summary :title="__('Inventory dashboard')">
    <div class="erp-kpi-grid">
        @foreach ($kpis as $kpi)
            <x-admin.kpi-widget
                :label="$kpi['label']"
                :value="$kpi['value']"
                :icon="$kpi['icon'] ?? 'cube'"
                :hint="$kpi['hint'] ?? null"
            />
        @endforeach
    </div>
</x-admin.collapsible-summary>
