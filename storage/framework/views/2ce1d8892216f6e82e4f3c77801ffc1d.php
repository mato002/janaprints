<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $embedded = WorkspaceEmbed::isEmbedded();
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
        'embedded' => $embedded ? '1' : null,
    ]);
    $hubBackUrl = route('admin.workspaces.administration.section', ['section' => 'configuration', 'tab' => 'document-types']);
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Document Types'),'breadcrumbs' => $embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ['label' => __('Document Types')],
    ],'useWorkspaceNavigation' => ! $embedded] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (! ($embedded)): ?>
        <?php echo $__env->make('admin.settings.partials.hub-toolbar', [
            'title' => __('Document Types'),
            'description' => __('Central registry for ERP document classification, numbering, approvals, and retention.'),
            'backUrl' => $hubBackUrl,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php echo $__env->make('admin.settings.partials.scope-selector', [
        'action' => route('admin.settings.document-types.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
        'branchEmptyLabel' => __('Company-wide default'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('admin.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">
                <?php echo e(__(':count document types registered', ['count' => count($rows)])); ?>

            </p>
            <?php if($canCreate): ?>
                <a
                    href="<?php echo e(WorkspaceEmbed::url(route('admin.settings.document-types.create', $scopeQuery))); ?>"
                    <?php if($embedded): ?> data-turbo-frame="module-workspace-content" <?php endif; ?>
                    class="erp-btn erp-btn--primary"
                >
                    <?php echo e(__('Create Document Type')); ?>

                </a>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="py-3 pr-3"><?php echo e(__('Document Type')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Module')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Prefix')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Number Series')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Approval')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Retention')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Status')); ?></th>
                        <th class="py-3 pl-2 text-right"><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-t border-erp-border">
                            <td class="py-3 pr-3">
                                <div class="font-medium text-slate-900"><?php echo e($row['name']); ?></div>
                                <div class="text-xs text-slate-500"><?php echo e($row['code']); ?></div>
                            </td>
                            <td class="py-3 px-2"><?php echo e($row['module']); ?></td>
                            <td class="py-3 px-2 font-mono text-xs"><?php echo e($row['prefix'] ?: '—'); ?></td>
                            <td class="py-3 px-2 text-xs"><?php echo e($row['number_series']); ?></td>
                            <td class="py-3 px-2 text-xs">
                                <?php if($row['approval_required']): ?>
                                    <span class="text-amber-700"><?php echo e($row['approval_rule']); ?></span>
                                <?php else: ?>
                                    <span class="text-slate-500"><?php echo e(__('Not required')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 text-xs"><?php echo e($row['retention_period']); ?></td>
                            <td class="py-3 px-2">
                                <?php if($row['is_active']): ?>
                                    <span class="erp-badge erp-badge--success"><?php echo e($row['status']); ?></span>
                                <?php else: ?>
                                    <span class="erp-badge erp-badge--muted"><?php echo e($row['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 pl-2 text-right">
                                <div class="flex justify-end gap-2">
                                    <?php if($canEdit): ?>
                                        <a
                                            href="<?php echo e(WorkspaceEmbed::url(route('admin.settings.document-types.edit', ['documentTypeDefinition' => $row['id']] + $scopeQuery))); ?>"
                                            <?php if($embedded): ?> data-turbo-frame="module-workspace-content" <?php endif; ?>
                                            class="erp-btn erp-btn--ghost erp-btn--sm"
                                        >
                                            <?php echo e(__('Edit')); ?>

                                        </a>
                                    <?php endif; ?>
                                    <?php if($row['is_active'] && $canDeactivate): ?>
                                        <form
                                            method="POST"
                                            action="<?php echo e(WorkspaceEmbed::url(route('admin.settings.document-types.deactivate', ['documentTypeDefinition' => $row['id']] + $scopeQuery))); ?>"
                                            <?php if($embedded): ?> data-turbo-frame="module-workspace-content" <?php endif; ?>
                                            onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Deactivate this document type?'))->toHtml() ?>)"
                                        >
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <?php if($embedded): ?>
                                                <input type="hidden" name="embedded" value="1">
                                            <?php endif; ?>
                                            <button type="submit" class="erp-btn erp-btn--ghost erp-btn--sm text-red-600">
                                                <?php echo e(__('Deactivate')); ?>

                                            </button>
                                        </form>
                                    <?php elseif(! $row['is_active'] && $canActivate): ?>
                                        <form
                                            method="POST"
                                            action="<?php echo e(WorkspaceEmbed::url(route('admin.settings.document-types.activate', ['documentTypeDefinition' => $row['id']] + $scopeQuery))); ?>"
                                            <?php if($embedded): ?> data-turbo-frame="module-workspace-content" <?php endif; ?>
                                        >
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <?php if($embedded): ?>
                                                <input type="hidden" name="embedded" value="1">
                                            <?php endif; ?>
                                            <button type="submit" class="erp-btn erp-btn--ghost erp-btn--sm text-emerald-700">
                                                <?php echo e(__('Activate')); ?>

                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">
                                <?php echo e(__('No document types configured for this scope.')); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\document-types\index.blade.php ENDPATH**/ ?>