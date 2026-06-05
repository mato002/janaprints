<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.procurement.reports.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.reports.partials.export-status')

    @include('admin.procurement.reports.partials.readiness-table', ['readiness' => $readiness, 'report_ready' => $report_ready])

    @include('admin.procurement.reports.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'warehouses' => $warehouses,
        'categories' => $categories,
        'suppliers' => $suppliers,
    ])

    @include('admin.procurement.reports.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.procurement.reports.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.procurement.reports.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'tabs' => $tabs,
        'filters' => $filters,
    ])
</x-admin-layout>
