<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Catalogue'),'maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Catalogue')),'maxWidth' => '5xl']); ?>
    <div class="space-y-4">
        <p class="text-sm text-slate-600"><?php echo e(__('Browse inventory items with operational stock status — without leaving the store desk.')); ?></p>

        <form method="GET" action="<?php echo e(route('admin.store.desk.catalogue')); ?>" class="flex flex-wrap items-center gap-2">
            <input
                type="search"
                name="search"
                value="<?php echo e($search); ?>"
                class="erp-input min-w-[14rem] flex-1"
                placeholder="<?php echo e(__('Search SKU or item name…')); ?>"
            >
            <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Search')); ?></button>
            <?php if($search !== ''): ?>
                <a href="<?php echo e(route('admin.store.desk.catalogue')); ?>" class="erp-btn-ghost text-sm"><?php echo e(__('Clear')); ?></a>
            <?php endif; ?>
        </form>

        <div class="overflow-x-auto rounded-lg border border-erp-border">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Item')); ?></th>
                        <th><?php echo e(__('SKU')); ?></th>
                        <th class="text-right"><?php echo e(__('Available')); ?></th>
                        <th class="text-right"><?php echo e(__('Reserved')); ?></th>
                        <th><?php echo e(__('Warehouse')); ?></th>
                        <th><?php echo e(__('Shelf')); ?></th>
                        <th><?php echo e(__('Category')); ?></th>
                        <th><?php echo e(__('Role')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php ($item = $row['item']); ?>
                        <tr>
                            <td class="font-medium"><?php echo e($item->item_name); ?></td>
                            <td class="font-mono text-xs"><?php echo e($item->sku); ?></td>
                            <td class="text-right font-mono text-xs tabular-nums"><?php echo e(number_format($row['available'], 2)); ?></td>
                            <td class="text-right font-mono text-xs tabular-nums"><?php echo e(number_format($row['reserved'], 2)); ?></td>
                            <td><?php echo e($row['warehouse'] ?? '—'); ?></td>
                            <td><?php echo e($row['shelf'] ?? '—'); ?></td>
                            <td><?php echo e($item->category?->name ?? '—'); ?></td>
                            <td>
                                <?php if($item->stock_role): ?>
                                    <span class="erp-badge <?php echo e($item->stock_role->badgeClass()); ?>"><?php echo e($item->stock_role->label()); ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="py-6 text-center text-sm text-slate-500"><?php echo e(__('No items match your search.')); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($items->hasPages()): ?>
            <div class="text-sm"><?php echo e($items->links()); ?></div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/store/desk/catalogue-modal.blade.php ENDPATH**/ ?>