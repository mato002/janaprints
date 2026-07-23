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
    <div class="job-360 w-full min-w-0" data-turbo-frame="erp-main">
        {{-- Zone 1: Hero Header --}}
        @include('admin.production.job-cards.workspace.header', [
            'jobCard' => $jobCard,
            'header' => $header,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'dispatchSummary' => $dispatchSummary,
            'workflowPresentation' => $workflowPresentation,
            'executionState' => $workspace['execution_state'] ?? [],
            'primaryAction' => $workspace['primary_action'] ?? null,
            'secondaryActions' => $workspace['secondary_actions'] ?? [],
        ])

        {{-- Material readiness gate — always visible, before timeline/blockers --}}
        @include('admin.production.job-cards.workspace.partials.material-readiness-banner', [
            'jobCard' => $jobCard,
            'materialReadiness' => $workspace['material_readiness'] ?? null,
        ])

        {{-- Zone 2: Workflow --}}
        @include('admin.production.job-cards.workspace.partials.production-stage-timeline', [
            'jobCard' => $jobCard,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'readinessChecklist' => $workspace['readiness_checklist'] ?? [],
            'dispatchSummary' => $dispatchSummary,
            'workflowPresentation' => $workflowPresentation,
            'materialReadiness' => $workspace['material_readiness'] ?? null,
        ])

        {{-- Zone 3: Blockers --}}
        @include('admin.production.job-cards.workspace.partials.blockers-panel', [
            'jobCard' => $jobCard,
            'workflowPresentation' => $workflowPresentation,
            'controlAlerts' => $workspace['control_alerts'] ?? [],
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'materialReadiness' => $workspace['material_readiness'] ?? null,
        ])

        {{-- Collapsible performance metrics --}}
        @include('admin.production.job-cards.workspace.partials.performance-section', [
            'kpis' => $workspace['kpis'] ?? [],
        ])

        {{-- Tab navigation --}}
        @include('admin.production.job-cards.workspace.tabs-nav', [
            'tabs' => $workspace['tabs'],
            'workspace' => $workspace,
        ])

        {{-- Tab content (Operations / Commercial / History zones live on Overview) --}}
        <div class="job-360__panel mt-4">
            @include('admin.production.job-cards.workspace.tabs.' . $activeTab, [
                'jobCard' => $jobCard,
                'tabData' => $tabData,
                'activeTab' => $activeTab,
                'header' => $header,
                'workflowPresentation' => $workflowPresentation,
                'executionState' => $workspace['execution_state'] ?? [],
                'assignableMachines' => $workspace['assignable_machines'] ?? collect(),
                'dispatchSummary' => $dispatchSummary,
            ])
        </div>
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
