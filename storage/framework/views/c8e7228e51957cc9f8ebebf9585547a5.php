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
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Cost Driver Breakdown')); ?></h2>

    <?php if($dashboard['cost_drivers']['has_data']): ?>
        <?php
            $total = max(1, (float) $dashboard['cost_drivers']['total']);
        ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $dashboard['cost_drivers']['available']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $share = round(((float) $driver['amount'] / $total) * 100, 1); ?>
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-erp-primary"><?php echo e($driver['label']); ?></span>
                        <span class="tabular-nums text-slate-600">KES <?php echo e(number_format($driver['amount'], 0)); ?> <span class="text-xs text-slate-400">(<?php echo e($share); ?>%)</span></span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-erp-accent" style="width: <?php echo e(min(100, $share)); ?>%"></div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <p class="mt-4 text-xs text-slate-500"><?php echo e(__('Total production cost in scope: KES :amount', ['amount' => number_format($dashboard['cost_drivers']['total'], 0)])); ?></p>
    <?php else: ?>
        <p class="text-sm text-slate-500"><?php echo e(__('Cost driver breakdown will appear once detailed costing inputs are available.')); ?></p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\costing\command-center\cost-drivers.blade.php ENDPATH**/ ?>