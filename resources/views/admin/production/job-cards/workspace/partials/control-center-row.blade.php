{{-- Blockers + Workflow + KPIs in a balanced 12-column row --}}
<div class="mb-3 grid grid-cols-1 gap-3 lg:grid-cols-12">
    <div class="lg:col-span-4">
        @include('admin.production.job-cards.workspace.partials.blockers-panel', [
            'jobCard' => $jobCard,
            'workflowPresentation' => $workflowPresentation,
            'controlAlerts' => $controlAlerts,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'materialReadiness' => $materialReadiness,
            'executionState' => $executionState,
        ])
    </div>

    <div class="space-y-2 lg:col-span-8">
        @include('admin.production.job-cards.workspace.partials.production-stage-timeline', [
            'jobCard' => $jobCard,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'readinessChecklist' => $readinessChecklist,
            'dispatchSummary' => $dispatchSummary,
            'workflowPresentation' => $workflowPresentation,
            'materialReadiness' => $materialReadiness,
        ])

        @include('admin.production.job-cards.workspace.partials.performance-section', [
            'kpis' => $kpis,
        ])
    </div>
</div>
