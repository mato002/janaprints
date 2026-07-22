<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Reorder alerts'),'maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Reorder alerts')),'maxWidth' => '5xl']); ?>
    <div class="space-y-4">
        <p class="text-sm text-slate-600"><?php echo e(__('Open low-stock alerts — acknowledge or resolve from this modal.')); ?></p>

        <form method="GET" action="<?php echo e(route('admin.store.desk.reorder-alerts')); ?>" class="flex flex-wrap items-center gap-2">
            <input
                type="search"
                name="search"
                value="<?php echo e($search); ?>"
                class="erp-input min-w-[14rem] flex-1"
                placeholder="<?php echo e(__('Search SKU or item name…')); ?>"
            >
            <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Search')); ?></button>
            <?php if($search !== ''): ?>
                <a href="<?php echo e(route('admin.store.desk.reorder-alerts')); ?>" class="erp-btn-ghost text-sm"><?php echo e(__('Clear')); ?></a>
            <?php endif; ?>
        </form>

        <div class="overflow-x-auto rounded-lg border border-erp-border">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Item')); ?></th>
                        <th><?php echo e(__('Warehouse')); ?></th>
                        <th class="text-right"><?php echo e(__('Current')); ?></th>
                        <th class="text-right"><?php echo e(__('Reorder')); ?></th>
                        <th class="text-right"><?php echo e(__('Shortage')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="font-medium"><?php echo e($alert->inventoryItem?->item_name ?? '—'); ?></span>
                                <span class="block font-mono text-[11px] text-slate-500"><?php echo e($alert->inventoryItem?->sku); ?></span>
                            </td>
                            <td><?php echo e($alert->warehouse?->name ?? '—'); ?></td>
                            <td class="text-right font-mono text-xs"><?php echo e(number_format((float) $alert->current_quantity, 2)); ?></td>
                            <td class="text-right font-mono text-xs"><?php echo e(number_format((float) $alert->reorder_level, 2)); ?></td>
                            <td class="text-right font-mono text-xs"><?php echo e(number_format($alert->shortageQuantity(), 2)); ?></td>
                            <td><?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $alert->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($alert->status->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?></td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('acknowledge', $alert)): ?>
                                        <form method="POST" action="<?php echo e(route('admin.inventory.alerts.acknowledge', $alert)); ?>" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="from" value="store-desk">
                                            <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Acknowledge')); ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('resolve', $alert)): ?>
                                        <form method="POST" action="<?php echo e(route('admin.inventory.alerts.resolve', $alert)); ?>" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="from" value="store-desk">
                                            <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Resolve')); ?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-6 text-center text-sm text-slate-500"><?php echo e(__('No open reorder alerts.')); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($alerts->hasPages()): ?>
            <div class="text-sm"><?php echo e($alerts->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/store/desk/reorder-alerts-modal.blade.php ENDPATH**/ ?>