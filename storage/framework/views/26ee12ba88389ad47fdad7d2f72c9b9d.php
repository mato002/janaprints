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
    <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e($title); ?></h3>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Item')); ?></th>
                    <th><?php echo e(__('Warehouse')); ?></th>
                    <th><?php echo e(__('Balance')); ?></th>
                    <th><?php echo e(__('Daily consumption')); ?></th>
                    <th><?php echo e(__('Days to depletion')); ?></th>
                    <th><?php echo e(__('Risk')); ?></th>
                    <th><?php echo e(__('Class')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $snapshots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $snapshot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <div class="font-medium"><?php echo e($snapshot->inventoryItem?->item_name); ?></div>
                            <div class="font-mono text-[11px] text-slate-500"><?php echo e($snapshot->inventoryItem?->sku); ?></div>
                        </td>
                        <td><?php echo e($snapshot->warehouse?->name ?? '—'); ?></td>
                        <td class="tabular-nums"><?php echo e(number_format((float) $snapshot->closing_balance, 3)); ?></td>
                        <td class="tabular-nums"><?php echo e(number_format((float) $snapshot->average_daily_consumption, 4)); ?></td>
                        <td class="tabular-nums"><?php echo e($snapshot->days_to_depletion !== null ? number_format((float) $snapshot->days_to_depletion, 1) : '—'); ?></td>
                        <td><?php echo e($snapshot->risk_level?->label()); ?></td>
                        <td><?php echo e($snapshot->velocity_class?->label()); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center text-slate-500"><?php echo e($empty); ?></td></tr>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\intelligence\partials\snapshot-table.blade.php ENDPATH**/ ?>