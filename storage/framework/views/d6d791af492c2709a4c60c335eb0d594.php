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
                    <th><?php echo e(__('Machine')); ?></th>
                    <th><?php echo e(__('Current util.')); ?></th>
                    <th><?php echo e(__('Forecast util.')); ?></th>
                    <th><?php echo e(__('Status')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($row['machine_name'] ?? '—'); ?></td>
                        <td><?php echo e(number_format((float) ($row['current_utilization_percent'] ?? 0), 1)); ?>%</td>
                        <td><?php echo e(($row['forecast_utilization_percent'] ?? null) !== null ? number_format((float) $row['forecast_utilization_percent'], 1).'%' : '—'); ?></td>
                        <td>
                            <?php if($row['is_bottleneck'] ?? false): ?>
                                <span class="erp-badge"><?php echo e(__('Bottleneck')); ?></span>
                            <?php elseif($row['is_underutilized'] ?? false): ?>
                                <span class="erp-badge"><?php echo e(__('Underutilized')); ?></span>
                            <?php else: ?>
                                <span class="erp-badge"><?php echo e(__('Normal')); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center text-slate-500 py-6"><?php echo e(__('No machine capacity data yet.')); ?></td></tr>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\partials\executive-capacity-table.blade.php ENDPATH**/ ?>