<?php
    $header = $workspace['header'];
    $activeTab = $workspace['active_tab'];
    $tabData = $workspace['tab_data'];
    $completion = $workspace['completion'] ?? ['eligible' => false, 'blockers' => []];
    $hasPostedOutput = $workspace['has_posted_output'] ?? false;
    $dispatchSummary = $workspace['dispatch_summary'] ?? null;
    $workflowPresentation = $workspace['workflow_presentation'] ?? null;
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $header['job_number'],'breadcrumbs' => [
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Production Floor'), 'url' => route('admin.production.floor')],
        ['label' => $header['job_number']],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="job-360 w-full min-w-0 overflow-visible pb-16" data-turbo-frame="erp-main">
        <div class="job-360-chrome">
            <?php echo $__env->make('admin.production.job-cards.workspace.partials.mes-dashboard', [
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
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="job-360-tabs-sticky">
                <?php echo $__env->make('admin.production.job-cards.workspace.tabs-nav', [
                    'tabs' => $workspace['tabs'],
                    'workspace' => $workspace,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <div class="job-360__panel mt-2">
            <?php echo $__env->make('admin.production.job-cards.workspace.tabs.' . $activeTab, [
                'jobCard' => $jobCard,
                'tabData' => $tabData,
                'activeTab' => $activeTab,
                'header' => $header,
                'workflowPresentation' => $workflowPresentation,
                'executionState' => $workspace['execution_state'] ?? [],
                'assignableMachines' => $workspace['assignable_machines'] ?? collect(),
                'dispatchSummary' => $dispatchSummary,
                'kpis' => $workspace['kpis'] ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.floating-action-bar', [
            'jobCard' => $jobCard,
            'executionState' => $workspace['execution_state'] ?? [],
            'primaryAction' => $workspace['primary_action'] ?? null,
            'linkActions' => $workspace['link_actions'] ?? [],
            'completion' => $completion,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production.outputs.post')): ?>
        <?php if (! ($activeTab === 'outputs')): ?>
            <?php echo $__env->make('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
                'jobCard' => $jobCard,
                'completion' => $completion,
                'finishedItems' => $workspace['finished_items'] ?? collect(),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/show.blade.php ENDPATH**/ ?>