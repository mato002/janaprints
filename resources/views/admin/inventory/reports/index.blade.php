<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.inventory.reports.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.reports.partials.export-status')

    @include('admin.inventory.reports.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'warehouses' => $warehouses,
        'categories' => $categories,
        'suppliers' => $suppliers,
        'items' => $items,
    ])

    @include('admin.inventory.reports.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.inventory.reports.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.inventory.reports.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'tabs' => $tabs,
        'filters' => $filters,
    ])
</x-admin-layout>
