<?php
    $header = $workspace['header'];
    $activeTab = $workspace['active_tab'];
    $tabData = $workspace['tab_data'];
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
    <div class="job-360 w-full min-w-0" data-turbo-frame="erp-main">
        <?php echo $__env->make('admin.production.job-cards.workspace.header', [
            'jobCard' => $jobCard,
            'header' => $header,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.workflow-bar', [
            'jobCard' => $jobCard,
            'primaryAction' => $workspace['primary_action'] ?? null,
            'secondaryActions' => $workspace['secondary_actions'] ?? [],
            'linkActions' => $workspace['link_actions'] ?? [],
            'completion' => $workspace['completion'] ?? ['eligible' => false, 'blockers' => []],
            'finishedItems' => $workspace['finished_items'] ?? collect(),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.control-alerts', ['alerts' => $workspace['control_alerts'] ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if($activeTab === 'overview'): ?>
            <?php echo $__env->make('admin.crm.customers.workspace.kpi-strip', ['kpis' => $workspace['kpis']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>

        <?php echo $__env->make('admin.production.job-cards.workspace.tabs-nav', [
            'tabs' => $workspace['tabs'],
            'workspace' => $workspace,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="job-360__panel mt-4">
            <?php echo $__env->make('admin.production.job-cards.workspace.tabs.' . $activeTab, [
                'jobCard' => $jobCard,
                'tabData' => $tabData,
                'activeTab' => $activeTab,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
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