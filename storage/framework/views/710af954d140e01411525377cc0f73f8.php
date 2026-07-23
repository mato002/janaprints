<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
    $activeRuleKey = request('rule');
    $activeRule = $activeRuleKey
        ? $rows->first(fn (array $row) => $row['rule_type'] === $activeRuleKey)
        : null;
    $embedded = WorkspaceEmbed::isEmbedded();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $activeRule ? $activeRule['label'] : __('Approval Rules'),'breadcrumbs' => $embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance')],
        ...($activeRule ? [['label' => $activeRule['label']]] : []),
    ],'useWorkspaceNavigation' => ! $embedded] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if($activeRule): ?>
        <?php echo $__env->make('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.approvals.index', ['rule' => $activeRuleKey] + $scopeQuery),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.settings.approvals.partials.workspace', [
            'row' => $activeRule,
            'canManage' => $canManage,
            'companyId' => $companyId,
            'branchId' => $branchId,
            'roles' => $roles,
            'permissions' => $permissions,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php if (! ($embedded)): ?>
            <?php echo $__env->make('admin.settings.partials.hub-toolbar', [
                'title' => __('Approval Rules'),
                'description' => __('Choose a rule type to configure amount and discount thresholds, approver roles, and permissions.'),
                'backUrl' => $hubBackUrl,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>

        <?php echo $__env->make('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.approvals.index'),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.settings.approvals.partials.landing', [
            'rows' => $rows,
            'companyId' => $companyId,
            'branchId' => $branchId,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\approvals\index.blade.php ENDPATH**/ ?>