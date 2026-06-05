<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.commercial.pos.intelligence.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.reports.partials.export-status')

    @include('admin.commercial.reports.sales.partials.readiness-table', [
        'readiness' => $readiness,
        'report_ready' => $report_ready,
        'context' => __('POS intelligence'),
    ])

    @include('admin.commercial.pos.intelligence.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'cashiers' => $cashiers,
    ])

    @include('admin.commercial.pos.intelligence.partials.kpi-strip', [
        'title' => __('POS Dashboard'),
        'kpis' => $dashboard_kpis,
    ])

    @include('admin.commercial.pos.intelligence.partials.kpi-strip', [
        'title' => __('Operational Metrics'),
        'kpis' => $metrics,
    ])

    @include('admin.commercial.pos.intelligence.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.commercial.pos.intelligence.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'tabs' => $tabs,
        'filters' => $filters,
    ])
</x-admin-layout>
