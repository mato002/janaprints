
<div class="mes-dashboard" aria-label="<?php echo e(__('Job control panel')); ?>">
    <?php echo $__env->make('admin.production.job-cards.workspace.header', [
        'jobCard' => $jobCard,
        'header' => $header,
        'completion' => $completion,
        'hasPostedOutput' => $hasPostedOutput,
        'dispatchSummary' => $dispatchSummary,
        'workflowPresentation' => $workflowPresentation,
        'executionState' => $executionState,
        'primaryAction' => $primaryAction,
        'secondaryActions' => $secondaryActions,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mes-dashboard__row mes-dashboard__row--split">
        <?php echo $__env->make('admin.production.job-cards.workspace.partials.blockers-panel', [
            'jobCard' => $jobCard,
            'workflowPresentation' => $workflowPresentation,
            'controlAlerts' => $controlAlerts,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'materialReadiness' => $materialReadiness,
            'executionState' => $executionState,
            'compact' => true,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.production-stage-timeline', [
            'jobCard' => $jobCard,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'readinessChecklist' => $readinessChecklist,
            'dispatchSummary' => $dispatchSummary,
            'workflowPresentation' => $workflowPresentation,
            'materialReadiness' => $materialReadiness,
            'compact' => true,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="mes-dashboard__row mes-dashboard__row--widgets">
        <?php
            $materialReadiness = is_array($materialReadiness ?? null) ? $materialReadiness : null;
        ?>

        <?php if($materialReadiness): ?>
            <a
                href="<?php echo e($materialReadiness['materials_url'] ?? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials'])); ?>"
                class="mes-kpi mes-kpi--materials"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
                title="<?php echo e($materialReadiness['detail'] ?? ''); ?>"
            >
                <span class="mes-kpi__label"><?php echo e(__('Materials')); ?></span>
                <span class="mes-kpi__value">
                    <?php if($materialReadiness['has_requirements'] ?? false): ?>
                        <span class="mes-kpi__stat mes-kpi__stat--ok">✓ <?php echo e($materialReadiness['ready_count'] ?? 0); ?></span>
                        <?php if(($materialReadiness['short_count'] ?? 0) > 0): ?>
                            <span class="mes-kpi__stat mes-kpi__stat--warn">⚠ <?php echo e($materialReadiness['short_count']); ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="mes-kpi__stat"><?php echo e(__('N/A')); ?></span>
                    <?php endif; ?>
                </span>
            </a>
        <?php endif; ?>

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.performance-section', [
            'kpis' => $kpis,
            'compact' => true,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mes-widget mes-widget--commercial">
            <?php echo $__env->make('admin.production.job-cards.workspace.partials.commercial-chips', [
                'jobCard' => $jobCard,
                'tabData' => $tabData ?? [],
                'dispatchSummary' => $dispatchSummary,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/mes-dashboard.blade.php ENDPATH**/ ?>