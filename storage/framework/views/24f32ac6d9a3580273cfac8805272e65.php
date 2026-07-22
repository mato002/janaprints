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
    <div class="overflow-x-auto p-4">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Branch')); ?></th>
                    <th><?php echo e(__('Revenue')); ?></th>
                    <th><?php echo e(__('Collections')); ?></th>
                    <th><?php echo e(__('Production cost')); ?></th>
                    <th><?php echo e(__('Estimated profit')); ?></th>
                    <th><?php echo e(__('Margin')); ?></th>
                    <th><?php echo e(__('Jobs')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($row['branch_name'] ?? '—'); ?></td>
                        <td class="font-mono"><?php echo e(number_format($row['revenue'] ?? 0, 2)); ?></td>
                        <td class="font-mono"><?php echo e(number_format($row['collections'] ?? 0, 2)); ?></td>
                        <td class="font-mono"><?php echo e(number_format($row['production_cost'] ?? 0, 2)); ?></td>
                        <td class="font-mono"><?php echo e(number_format($row['estimated_profit'] ?? 0, 2)); ?></td>
                        <td><?php echo e(number_format($row['margin_percent'] ?? 0, 1)); ?>%</td>
                        <td><?php echo e($row['jobs_count'] ?? 0); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="py-8 text-center text-slate-500"><?php echo e(__('No branch profitability data in this scope.')); ?></td></tr>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\commercial-intelligence\partials\branch-profitability.blade.php ENDPATH**/ ?>