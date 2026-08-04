
<div class="mb-3 grid grid-cols-1 gap-3 lg:grid-cols-12">
    <div class="lg:col-span-4">
        <?php echo $__env->make('admin.production.job-cards.workspace.partials.blockers-panel', [
            'jobCard' => $jobCard,
            'workflowPresentation' => $workflowPresentation,
            'controlAlerts' => $controlAlerts,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'materialReadiness' => $materialReadiness,
            'executionState' => $executionState,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="space-y-2 lg:col-span-8">
        <?php echo $__env->make('admin.production.job-cards.workspace.partials.production-stage-timeline', [
            'jobCard' => $jobCard,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'readinessChecklist' => $readinessChecklist,
            'dispatchSummary' => $dispatchSummary,
            'workflowPresentation' => $workflowPresentation,
            'materialReadiness' => $materialReadiness,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.performance-section', [
            'kpis' => $kpis,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\control-center-row.blade.php ENDPATH**/ ?>