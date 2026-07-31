<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.commercial.reports.sales.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.reports.partials.export-status')

    @include('admin.commercial.reports.sales.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'customers' => $customers,
        'salespersons' => $salespersons,
    ])

    @include('admin.commercial.reports.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.commercial.reports.sales.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.commercial.reports.sales.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'tabs' => $tabs,
        'filters' => $filters,
    ])
</x-admin-layout>
