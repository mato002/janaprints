<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            <span class="erp-badge bg-slate-100 text-slate-700">{{ __('Workforce analytics') }}</span>
            @if ($can_export)
                <a href="{{ route('admin.reports.hr.print', $filters) }}" target="_blank" class="erp-btn-secondary text-xs">
                    {{ __('Print') }}
                </a>
            @endif
        </x-slot>
    </x-admin.page-header>

    @include('admin.reports.hr.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'departments' => $departments,
        'jobTitles' => $jobTitles,
        'employees' => $employees,
        'employmentStatuses' => $employmentStatuses,
        'can_export' => $can_export,
    ])

    @include('admin.reports.hr.partials.catalog', ['catalog' => $catalog])

    @include('admin.reports.hr.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.reports.hr.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
    ])
</x-admin-layout>
