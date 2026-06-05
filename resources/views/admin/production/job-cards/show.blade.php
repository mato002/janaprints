@php
    $header = $workspace['header'];
    $activeTab = $workspace['active_tab'];
    $tabData = $workspace['tab_data'];
@endphp

<x-admin-layout
    :title="$header['job_number']"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Job Cards'), 'url' => route('admin.production.job-cards.index')],
        ['label' => $header['job_number']],
    ]"
>
    <div class="job-360 w-full min-w-0" data-turbo-frame="erp-main">
        @include('admin.production.job-cards.workspace.header', [
            'jobCard' => $jobCard,
            'header' => $header,
            'quickActions' => $workspace['quick_actions'],
        ])

        @include('admin.production.job-cards.workspace.partials.control-alerts', ['alerts' => $workspace['control_alerts'] ?? []])

        @include('admin.crm.customers.workspace.kpi-strip', ['kpis' => $workspace['kpis']])

        @include('admin.production.job-cards.workspace.tabs-nav', ['tabs' => $workspace['tabs']])

        <div class="job-360__panel mt-4">
            @include('admin.production.job-cards.workspace.tabs.' . $activeTab, [
                'jobCard' => $jobCard,
                'tabData' => $tabData,
                'activeTab' => $activeTab,
            ])
        </div>
    </div>
</x-admin-layout>
