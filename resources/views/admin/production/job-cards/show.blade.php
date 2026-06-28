@php
    $header = $workspace['header'];
    $activeTab = $workspace['active_tab'];
    $tabData = $workspace['tab_data'];
@endphp

<x-admin-layout
    :title="$header['job_number']"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Production Floor'), 'url' => route('admin.production.floor')],
        ['label' => $header['job_number']],
    ]"
>
    <div class="job-360 w-full min-w-0" data-turbo-frame="erp-main">
        @include('admin.production.job-cards.workspace.header', [
            'jobCard' => $jobCard,
            'header' => $header,
        ])

        @include('admin.production.job-cards.workspace.partials.workflow-bar', [
            'jobCard' => $jobCard,
            'primaryAction' => $workspace['primary_action'] ?? null,
            'secondaryActions' => $workspace['secondary_actions'] ?? [],
            'linkActions' => $workspace['link_actions'] ?? [],
            'completion' => $workspace['completion'] ?? ['eligible' => false, 'blockers' => []],
            'finishedItems' => $workspace['finished_items'] ?? collect(),
        ])

        @include('admin.production.job-cards.workspace.partials.control-alerts', ['alerts' => $workspace['control_alerts'] ?? []])

        @if ($activeTab === 'overview')
            @include('admin.crm.customers.workspace.kpi-strip', ['kpis' => $workspace['kpis']])
        @endif

        @include('admin.production.job-cards.workspace.tabs-nav', [
            'tabs' => $workspace['tabs'],
            'workspace' => $workspace,
        ])

        <div class="job-360__panel mt-4">
            @include('admin.production.job-cards.workspace.tabs.' . $activeTab, [
                'jobCard' => $jobCard,
                'tabData' => $tabData,
                'activeTab' => $activeTab,
            ])
        </div>
    </div>
</x-admin-layout>
