<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
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
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold"><?php echo e(__('Outsourced Jobs')); ?></h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Job')); ?></th><th><?php echo e(__('Vendor')); ?></th><th><?php echo e(__('Revenue')); ?></th><th><?php echo e(__('Vendor cost')); ?></th><th><?php echo e(__('Margin')); ?></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $data['jobs'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($row['job_number'] ?? '—'); ?></td>
                            <td><?php echo e($row['vendor_name'] ?? '—'); ?></td>
                            <td class="font-mono"><?php echo e(number_format($row['customer_revenue'] ?? 0, 2)); ?></td>
                            <td class="font-mono"><?php echo e(number_format($row['vendor_cost'] ?? 0, 2)); ?></td>
                            <td><?php echo e(number_format($row['estimated_margin_percent'] ?? 0, 1)); ?>%</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="py-6 text-center text-slate-500"><?php echo e(__('No outsourced jobs in scope.')); ?></td></tr>
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
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold"><?php echo e(__('By Vendor')); ?></h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th><?php echo e(__('Vendor')); ?></th><th><?php echo e(__('Jobs')); ?></th><th><?php echo e(__('Revenue')); ?></th><th><?php echo e(__('Cost')); ?></th><th><?php echo e(__('Profit')); ?></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $data['vendors'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($row['vendor_name'] ?? '—'); ?></td>
                            <td><?php echo e($row['job_count'] ?? 0); ?></td>
                            <td class="font-mono"><?php echo e(number_format($row['customer_revenue'] ?? 0, 2)); ?></td>
                            <td class="font-mono"><?php echo e(number_format($row['vendor_cost'] ?? 0, 2)); ?></td>
                            <td class="font-mono"><?php echo e(number_format($row['estimated_profit'] ?? 0, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="py-6 text-center text-slate-500"><?php echo e(__('No vendor data.')); ?></td></tr>
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
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\commercial-intelligence\partials\outsource-profitability.blade.php ENDPATH**/ ?>