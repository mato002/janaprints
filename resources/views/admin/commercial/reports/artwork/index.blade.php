<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.commercial.reports.artwork.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.reports.partials.export-status')

    @include('admin.commercial.reports.artwork.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'customers' => $customers,
        'designers' => $designers,
    ])

    @include('admin.commercial.reports.artwork.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.commercial.reports.artwork.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.commercial.reports.artwork.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'tabs' => $tabs,
        'filters' => $filters,
    ])
</x-admin-layout>
