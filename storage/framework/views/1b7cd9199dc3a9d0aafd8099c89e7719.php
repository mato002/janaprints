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
    <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('AR aging as of :date', ['date' => $profile['aging']['as_of_date'] ?? now()->toDateString()])); ?></h3>
    <dl class="space-y-2 text-sm">
        <?php $__currentLoopData = [
            'current' => __('Current'),
            '1_30' => __('1–30 days overdue'),
            '31_60' => __('31–60 days overdue'),
            '61_90' => __('61–90 days overdue'),
            '90_plus' => __('90+ days overdue'),
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex justify-between">
                <dt class="text-slate-500"><?php echo e($label); ?></dt>
                <dd class="font-mono"><?php echo e(number_format($aging[$key] ?? 0, 2)); ?></dd>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="flex justify-between border-t border-erp-border pt-2 font-semibold">
            <dt><?php echo e(__('Total open AR')); ?></dt>
            <dd class="font-mono"><?php echo e(number_format($profile['aging']['total'] ?? 0, 2)); ?></dd>
        </div>
        <div class="flex justify-between pt-2 text-slate-600">
            <dt><?php echo e(__('Ledger outstanding')); ?></dt>
            <dd class="font-mono"><?php echo e(number_format($profile['outstanding'] ?? 0, 2)); ?></dd>
        </div>
    </dl>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\partials\financial-aging.blade.php ENDPATH**/ ?>