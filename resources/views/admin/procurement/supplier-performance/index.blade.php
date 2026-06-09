<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.procurement.supplier-performance.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.reports.partials.export-status')

    @include('admin.procurement.supplier-performance.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'warehouses' => $warehouses,
        'categories' => $categories,
        'suppliers' => $suppliers,
    ])

    @include('admin.procurement.supplier-performance.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.procurement.supplier-performance.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.procurement.supplier-performance.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'tabs' => $tabs,
        'filters' => $filters,
    ])
</x-admin-layout>
