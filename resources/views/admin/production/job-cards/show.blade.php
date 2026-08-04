@php
    $header = $workspace['header'];
    $activeTab = $workspace['active_tab'];
    $tabData = $workspace['tab_data'];
    $completion = $workspace['completion'] ?? ['eligible' => false, 'blockers' => []];
    $hasPostedOutput = $workspace['has_posted_output'] ?? false;
    $dispatchSummary = $workspace['dispatch_summary'] ?? null;
    $workflowPresentation = $workspace['workflow_presentation'] ?? null;
@endphp

<x-admin-layout
    :title="$header['job_number']"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Production Floor'), 'url' => route('admin.production.floor')],
        ['label' => $header['job_number']],
    ]"
>
    <div class="job-360 w-full min-w-0 overflow-visible pb-16" data-turbo-frame="erp-main">
        @include('admin.production.job-cards.workspace.partials.mes-dashboard', [
            'jobCard' => $jobCard,
            'header' => $header,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'dispatchSummary' => $dispatchSummary,
            'workflowPresentation' => $workflowPresentation,
            'executionState' => $workspace['execution_state'] ?? [],
            'primaryAction' => $workspace['primary_action'] ?? null,
            'secondaryActions' => $workspace['secondary_actions'] ?? [],
            'controlAlerts' => $workspace['control_alerts'] ?? [],
            'materialReadiness' => $workspace['material_readiness'] ?? null,
            'readinessChecklist' => $workspace['readiness_checklist'] ?? [],
            'kpis' => $workspace['kpis'] ?? [],
            'tabData' => $tabData,
        ])

        <div class="job-360-tabs-sticky">
            @include('admin.production.job-cards.workspace.tabs-nav', [
                'tabs' => $workspace['tabs'],
                'workspace' => $workspace,
            ])
        </div>

        <div class="job-360__panel mt-2">
            @include('admin.production.job-cards.workspace.tabs.' . $activeTab, [
                'jobCard' => $jobCard,
                'tabData' => $tabData,
                'activeTab' => $activeTab,
                'header' => $header,
                'workflowPresentation' => $workflowPresentation,
                'executionState' => $workspace['execution_state'] ?? [],
                'assignableMachines' => $workspace['assignable_machines'] ?? collect(),
                'dispatchSummary' => $dispatchSummary,
                'kpis' => $workspace['kpis'] ?? [],
            ])
        </div>

        @include('admin.production.job-cards.workspace.partials.floating-action-bar', [
            'jobCard' => $jobCard,
            'executionState' => $workspace['execution_state'] ?? [],
            'primaryAction' => $workspace['primary_action'] ?? null,
            'linkActions' => $workspace['link_actions'] ?? [],
            'completion' => $completion,
        ])
    </div>

    @can('production.outputs.post')
        @unless ($activeTab === 'outputs')
            @include('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
                'jobCard' => $jobCard,
                'completion' => $completion,
                'finishedItems' => $workspace['finished_items'] ?? collect(),
            ])
        @endunless
    @endcan
</x-admin-layout>
