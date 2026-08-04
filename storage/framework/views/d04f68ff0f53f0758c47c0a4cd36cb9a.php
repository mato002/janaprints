<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Top Profitable Jobs')); ?></h2>
        <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Top 10 by gross profit in selected scope')); ?></p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Rank')); ?></th>
                    <th><?php echo e(__('Job Card')); ?></th>
                    <th><?php echo e(__('Customer')); ?></th>
                    <th class="text-right"><?php echo e(__('Revenue')); ?></th>
                    <th class="text-right"><?php echo e(__('Cost')); ?></th>
                    <th class="text-right"><?php echo e(__('Profit')); ?></th>
                    <th><?php echo e(__('Margin %')); ?></th>
                    <th class="erp-table-actions-col"><?php echo e(__('Action')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $dashboard['top_profitable']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="tabular-nums font-medium"><?php echo e($row['rank']); ?></td>
                        <td class="font-mono"><?php echo e($row['job_card_number']); ?></td>
                        <td><?php echo e($row['customer_name']); ?></td>
                        <td class="text-right tabular-nums">KES <?php echo e(number_format($row['revenue'], 0)); ?></td>
                        <td class="text-right tabular-nums">KES <?php echo e(number_format($row['cost'], 0)); ?></td>
                        <td class="text-right tabular-nums font-medium text-emerald-700">KES <?php echo e(number_format($row['profit'], 0)); ?></td>
                        <td>
                            <div class="flex flex-col gap-1">
                                <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $row['margin_variant']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['margin_variant'])]); ?><?php echo e(number_format($row['margin_percent'], 1)); ?>% <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginal4247c99dec2da459a56c896e70a73fc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4247c99dec2da459a56c896e70a73fc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.margin-bar','data' => ['percent' => $row['margin_percent'],'variant' => $row['margin_variant']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.margin-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['margin_percent']),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['margin_variant'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4247c99dec2da459a56c896e70a73fc2)): ?>
<?php $attributes = $__attributesOriginal4247c99dec2da459a56c896e70a73fc2; ?>
<?php unset($__attributesOriginal4247c99dec2da459a56c896e70a73fc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4247c99dec2da459a56c896e70a73fc2)): ?>
<?php $component = $__componentOriginal4247c99dec2da459a56c896e70a73fc2; ?>
<?php unset($__componentOriginal4247c99dec2da459a56c896e70a73fc2); ?>
<?php endif; ?>
                            </div>
                        </td>
                        <td class="erp-table-actions-col">
                            <?php if($row['job_360_url']): ?>
                                <a href="<?php echo e($row['job_360_url']); ?>" class="text-xs font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e(__('Open Job 360')); ?></a>
                            <?php elseif($row['costing_url']): ?>
                                <a href="<?php echo e($row['costing_url']); ?>" class="text-xs font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e(__('View costing')); ?></a>
                            <?php else: ?>
                                <span class="text-xs text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500"><?php echo e(__('No profitable jobs found yet.')); ?></td>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\costing\command-center\top-profitable.blade.php ENDPATH**/ ?>