@props(['title', 'widgets'])

<x-admin.collapsible-summary :title="$title">
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
</x-admin.collapsible-summary>
