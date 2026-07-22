<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Ink Profiles'),'breadcrumbs' => [
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Ink Intelligence'), 'url' => route('admin.printing-intelligence.ink')],
    ['label' => __('Ink Profiles')],
]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Ink Profiles'),'description' => __('Maintain ink costing profiles for PI3 ink estimation. No inventory or accounting mutations.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Ink Profiles')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Maintain ink costing profiles for PI3 ink estimation. No inventory or accounting mutations.'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('admin.printing-intelligence.ink')); ?>" class="erp-btn-secondary"><?php echo e(__('Back to Ink Intelligence')); ?></a>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <?php echo $__env->make('admin.printing-intelligence.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if($canManage): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4','xData' => '{ open: '.e($errors->any() && ! request()->query('edit') ? 'true' : 'false').' }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4','x-data' => '{ open: '.e($errors->any() && ! request()->query('edit') ? 'true' : 'false').' }']); ?>
            <div class="flex items-center justify-between gap-3 mb-3">
                <h3 class="font-medium"><?php echo e(__('Create Ink Profile')); ?></h3>
                <button type="button" class="erp-btn-secondary text-xs" @click="open = !open" x-text="open ? '<?php echo e(__('Hide form')); ?>' : '<?php echo e(__('Show form')); ?>'"></button>
            </div>
            <form method="POST" action="<?php echo e(route('admin.printing-intelligence.ink-profiles.store')); ?>" x-show="open" x-cloak>
                <?php echo csrf_field(); ?>
                <?php echo $__env->make('admin.printing-intelligence.ink-profiles.partials.form-fields', [
                    'inkTypes' => $inkTypes,
                    'inventoryItems' => $inventoryItems,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="mt-4">
                    <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Create profile')); ?></button>
                </div>
            </form>
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
    <?php endif; ?>

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
        <div class="overflow-x-auto">
            <table class="erp-table text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Name')); ?></th>
                        <th><?php echo e(__('Ink Type')); ?></th>
                        <th><?php echo e(__('Inventory Item')); ?></th>
                        <th><?php echo e(__('Cartridge Cost')); ?></th>
                        <th><?php echo e(__('Estimated ml')); ?></th>
                        <th><?php echo e(__('Cost/ml')); ?></th>
                        <th><?php echo e(__('Yield Pages')); ?></th>
                        <th><?php echo e(__('Yield m²')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <?php if($canManage): ?>
                            <th class="erp-table-actions-col"><?php echo e(__('Actions')); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses(['bg-slate-50' => ! $profile['active']]); ?>">
                            <td class="font-medium"><?php echo e($profile['name']); ?></td>
                            <td><?php echo e($profile['ink_type']); ?></td>
                            <td><?php echo e($profile['inventory_item'] ?? '—'); ?></td>
                            <td><?php echo e(number_format((float) $profile['cartridge_cost'], 2)); ?></td>
                            <td><?php echo e($profile['estimated_ml'] !== null ? number_format((float) $profile['estimated_ml'], 3) : '—'); ?></td>
                            <td><?php echo e($profile['cost_per_ml'] !== null ? number_format((float) $profile['cost_per_ml'], 4) : '—'); ?></td>
                            <td><?php echo e($profile['yield_per_page'] !== null ? number_format((float) $profile['yield_per_page'], 4) : '—'); ?></td>
                            <td><?php echo e($profile['yield_per_sq_m'] !== null ? number_format((float) $profile['yield_per_sq_m'], 4) : '—'); ?></td>
                            <td>
                                <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $profile['active'] ? 'success' : 'draft']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile['active'] ? 'success' : 'draft')]); ?>
                                    <?php echo e($profile['active'] ? __('Active') : __('Inactive')); ?>

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
                            </td>
                            <?php if($canManage): ?>
                                <td class="erp-table-actions-col align-top">
                                    <details class="text-xs" <?php if((string) request()->query('edit') === (string) $profile['id']): ?> open <?php endif; ?>>
                                        <summary class="cursor-pointer text-erp-primary font-medium"><?php echo e(__('Edit')); ?></summary>
                                        <div class="mt-3 min-w-[18rem] rounded border border-slate-200 bg-white p-3 shadow-sm">
                                            <form method="POST" action="<?php echo e(route('admin.printing-intelligence.ink-profiles.update', $profile['id'])); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <?php echo $__env->make('admin.printing-intelligence.ink-profiles.partials.form-fields', [
                                                    'profile' => $profile,
                                                    'inkTypes' => $inkTypes,
                                                    'inventoryItems' => $inventoryItems,
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Save')); ?></button>
                                                </div>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('admin.printing-intelligence.ink-profiles.destroy', $profile['id'])); ?>" class="mt-2" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from($profile['used_by_estimates'] ? __('Deactivate this profile? It is referenced by ink estimates.') : __('Remove this ink profile?'))->toHtml() ?>)">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="erp-btn-secondary text-xs text-red-700">
                                                    <?php echo e($profile['used_by_estimates'] ? __('Deactivate') : __('Delete')); ?>

                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($canManage ? 10 : 9); ?>" class="py-8 text-center text-slate-500">
                                <?php echo e(__('No ink profiles configured.')); ?>

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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\ink-profiles\index.blade.php ENDPATH**/ ?>