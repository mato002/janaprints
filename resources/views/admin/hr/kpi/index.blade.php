<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            <span class="erp-badge bg-slate-100 text-slate-700">{{ __('Workforce KPIs') }}</span>
            @include('admin.hr.kpi.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.hr.kpi.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'departments' => $departments,
        'jobTitles' => $jobTitles,
        'employees' => $employees,
        'employmentStatuses' => $employmentStatuses,
    ])

    @include('admin.hr.kpi.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.hr.kpi.partials.dimension-tabs', [
        'dimensions' => $dimensions,
        'active_dimension' => $active_dimension,
        'filters' => $filters,
    ])

    @include('admin.hr.kpi.partials.dimension-table', [
        'rows' => $dimension_rows,
        'active_dimension' => $active_dimension,
    ])

    @include('admin.hr.kpi.partials.rankings', ['rankings' => $rankings])

    @include('admin.hr.kpi.partials.rating-distribution', ['distribution' => $rating_distribution])
</x-admin-layout>
