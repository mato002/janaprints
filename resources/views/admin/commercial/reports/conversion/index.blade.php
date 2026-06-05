<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.commercial.reports.conversion.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.reports.partials.export-status')

    @include('admin.commercial.reports.sales.partials.readiness-table', [
        'readiness' => $readiness,
        'report_ready' => $report_ready,
        'context' => __('conversion reports'),
    ])

    @include('admin.commercial.reports.conversion.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'lead_sources' => $lead_sources,
        'salespersons' => $salespersons,
    ])

    @include('admin.commercial.reports.conversion.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.commercial.reports.conversion.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.commercial.reports.conversion.partials.tab-content', [
        'tab_data' => $tab_data,
        'has_production_pipeline' => $has_production_pipeline,
        'has_dispatch_pipeline' => $has_dispatch_pipeline,
    ])
</x-admin-layout>
