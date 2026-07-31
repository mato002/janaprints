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

    @include('admin.commercial.pos.intelligence.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'cashiers' => $cashiers,
        'report_views' => $report_views,
    ])

    @include('admin.commercial.reports.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.commercial.pos.intelligence.partials.tab-content', [
        'tab_data' => $tab_data,
        'report_label' => $report_label,
        'filters' => $filters,
    ])
</x-admin-layout>
