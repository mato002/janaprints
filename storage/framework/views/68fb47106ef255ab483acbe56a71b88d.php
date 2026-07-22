<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
    $embedded = WorkspaceEmbed::isEmbedded();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Number Series'),'breadcrumbs' => $embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ['label' => __('Number Series')],
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
            'title' => __('Document Numbering'),
            'description' => __('Configure prefixes, padding, and next numbers for each document type.'),
            'backUrl' => $hubBackUrl,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php echo $__env->make('admin.settings.partials.scope-selector', [
        'action' => route('admin.settings.numbering.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
        'branchEmptyLabel' => __('Company-wide default'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
        <?php if($canManage): ?>
            <form method="POST" action="<?php echo e(route('admin.settings.numbering.update')); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
                <?php if($branchId): ?>
                    <input type="hidden" name="branch_id" value="<?php echo e($branchId); ?>">
                <?php endif; ?>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="py-3 pr-3"><?php echo e(__('Document')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Prefix')); ?></th>
                        <th class="py-3 px-2 text-center"><?php echo e(__('Branch')); ?></th>
                        <th class="py-3 px-2 text-center"><?php echo e(__('Year')); ?></th>
                        <th class="py-3 px-2 text-center"><?php echo e(__('Month')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Padding')); ?></th>
                        <th class="py-3 px-2"><?php echo e(__('Next #')); ?></th>
                        <th class="py-3 px-2 text-center"><?php echo e(__('Active')); ?></th>
                        <th class="py-3 pl-2"><?php echo e(__('Preview')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-erp-border">
                    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="py-3 pr-3 align-top">
                                <p class="font-medium text-erp-primary"><?php echo e($row['label']); ?></p>
                                <p class="font-mono text-[11px] text-slate-400"><?php echo e($row['type_code']); ?></p>
                            </td>
                            <td class="py-3 px-2 align-top">
                                <?php if($canManage): ?>
                                    <input
                                        type="text"
                                        name="sequences[<?php echo e($row['document_type']); ?>][prefix]"
                                        value="<?php echo e($row['prefix']); ?>"
                                        class="erp-input w-24"
                                        maxlength="20"
                                    >
                                <?php else: ?>
                                    <?php echo e($row['prefix']); ?>

                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 text-center align-top">
                                <?php if($canManage): ?>
                                    <input type="hidden" name="sequences[<?php echo e($row['document_type']); ?>][include_branch]" value="0">
                                    <input type="checkbox" name="sequences[<?php echo e($row['document_type']); ?>][include_branch]" value="1" class="rounded border-erp-border text-erp-accent" <?php if($row['include_branch']): echo 'checked'; endif; ?>>
                                <?php else: ?>
                                    <?php echo e($row['include_branch'] ? __('Yes') : __('No')); ?>

                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 text-center align-top">
                                <?php if($canManage): ?>
                                    <input type="hidden" name="sequences[<?php echo e($row['document_type']); ?>][include_year]" value="0">
                                    <input type="checkbox" name="sequences[<?php echo e($row['document_type']); ?>][include_year]" value="1" class="rounded border-erp-border text-erp-accent" <?php if($row['include_year']): echo 'checked'; endif; ?>>
                                <?php else: ?>
                                    <?php echo e($row['include_year'] ? __('Yes') : __('No')); ?>

                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 text-center align-top">
                                <?php if($canManage): ?>
                                    <input type="hidden" name="sequences[<?php echo e($row['document_type']); ?>][include_month]" value="0">
                                    <input type="checkbox" name="sequences[<?php echo e($row['document_type']); ?>][include_month]" value="1" class="rounded border-erp-border text-erp-accent" <?php if($row['include_month']): echo 'checked'; endif; ?>>
                                <?php else: ?>
                                    <?php echo e($row['include_month'] ? __('Yes') : __('No')); ?>

                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 align-top">
                                <?php if($canManage): ?>
                                    <input type="number" name="sequences[<?php echo e($row['document_type']); ?>][padding]" value="<?php echo e($row['padding']); ?>" min="1" max="10" class="erp-input w-16">
                                <?php else: ?>
                                    <?php echo e($row['padding']); ?>

                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 align-top">
                                <?php if($canManage): ?>
                                    <input type="number" name="sequences[<?php echo e($row['document_type']); ?>][next_number]" value="<?php echo e($row['next_number']); ?>" min="1" class="erp-input w-24">
                                <?php else: ?>
                                    <?php echo e($row['next_number']); ?>

                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 text-center align-top">
                                <?php if($canManage): ?>
                                    <input type="hidden" name="sequences[<?php echo e($row['document_type']); ?>][active]" value="0">
                                    <input type="checkbox" name="sequences[<?php echo e($row['document_type']); ?>][active]" value="1" class="rounded border-erp-border text-erp-accent" <?php if($row['active']): echo 'checked'; endif; ?>>
                                <?php else: ?>
                                    <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $row['active'] ? 'success' : 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['active'] ? 'success' : 'danger')]); ?>
                                        <?php echo e($row['active'] ? __('Yes') : __('No')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 pl-2 align-top">
                                <code class="text-xs text-slate-600"><?php echo e($row['preview']); ?></code>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <?php if($canManage): ?>
                <div class="mt-6 border-t border-erp-border pt-6">
                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Save numbering')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\numbering\index.blade.php ENDPATH**/ ?>