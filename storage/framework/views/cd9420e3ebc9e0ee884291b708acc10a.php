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
        <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Branch Profitability')); ?></h2>
        <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Comparative branch performance in selected scope')); ?></p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Branch')); ?></th>
                    <th class="text-right"><?php echo e(__('Revenue')); ?></th>
                    <th class="text-right"><?php echo e(__('Cost')); ?></th>
                    <th class="text-right"><?php echo e(__('Profit')); ?></th>
                    <th><?php echo e(__('Margin %')); ?></th>
                    <th class="text-right"><?php echo e(__('Jobs Count')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $dashboard['branch_profitability']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-medium"><?php echo e($row['branch_name']); ?></td>
                        <td class="text-right tabular-nums">KES <?php echo e(number_format($row['revenue'], 0)); ?></td>
                        <td class="text-right tabular-nums">KES <?php echo e(number_format($row['cost'], 0)); ?></td>
                        <td class="text-right tabular-nums <?php echo e($row['profit'] >= 0 ? 'text-emerald-700' : 'text-red-700'); ?>">KES <?php echo e(number_format($row['profit'], 0)); ?></td>
                        <td class="min-w-[10rem]">
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
                        <td class="text-right tabular-nums"><?php echo e($row['job_count']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500"><?php echo e(__('No branch profitability data in this scope.')); ?></td>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\costing\command-center\branch-profitability.blade.php ENDPATH**/ ?>