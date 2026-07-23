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
    <h3 class="font-medium mb-3"><?php echo e($result['scenario_label'] ?? __('Scenario')); ?></h3>
    <div class="grid gap-4 md:grid-cols-2 text-sm">
        <div>
            <h4 class="text-xs uppercase text-slate-500 mb-2"><?php echo e(__('Baseline')); ?></h4>
            <dl class="space-y-1">
                <div class="flex justify-between"><dt><?php echo e(__('Revenue')); ?></dt><dd><?php echo e(number_format((float) ($result['baseline']['revenue'] ?? 0), 2)); ?></dd></div>
                <div class="flex justify-between"><dt><?php echo e(__('Cost')); ?></dt><dd><?php echo e(number_format((float) ($result['baseline']['cost'] ?? 0), 2)); ?></dd></div>
                <div class="flex justify-between"><dt><?php echo e(__('Profit')); ?></dt><dd><?php echo e(number_format((float) ($result['baseline']['profit'] ?? 0), 2)); ?></dd></div>
                <div class="flex justify-between"><dt><?php echo e(__('Margin')); ?></dt><dd><?php echo e(($result['baseline']['margin_percent'] ?? null) !== null ? number_format((float) $result['baseline']['margin_percent'], 1).'%' : '—'); ?></dd></div>
            </dl>
        </div>
        <div>
            <h4 class="text-xs uppercase text-slate-500 mb-2"><?php echo e(__('Simulated')); ?></h4>
            <dl class="space-y-1">
                <div class="flex justify-between"><dt><?php echo e(__('Revenue')); ?></dt><dd><?php echo e(number_format((float) ($result['simulated']['revenue'] ?? 0), 2)); ?></dd></div>
                <div class="flex justify-between"><dt><?php echo e(__('Cost')); ?></dt><dd><?php echo e(number_format((float) ($result['simulated']['cost'] ?? 0), 2)); ?></dd></div>
                <div class="flex justify-between"><dt><?php echo e(__('Profit')); ?></dt><dd><?php echo e(number_format((float) ($result['simulated']['profit'] ?? 0), 2)); ?></dd></div>
                <div class="flex justify-between"><dt><?php echo e(__('Margin')); ?></dt><dd><?php echo e(($result['simulated']['margin_percent'] ?? null) !== null ? number_format((float) $result['simulated']['margin_percent'], 1).'%' : '—'); ?></dd></div>
            </dl>
        </div>
    </div>
    <div class="mt-4 pt-4 border-t border-slate-100 text-sm">
        <strong><?php echo e(__('Impact')); ?>:</strong>
        <?php echo e(__('Profit delta')); ?> <?php echo e(number_format((float) ($result['impact']['profit_delta'] ?? 0), 2)); ?>,
        <?php echo e(__('Revenue delta')); ?> <?php echo e(number_format((float) ($result['impact']['revenue_delta'] ?? 0), 2)); ?>

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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\partials\executive-scenario-result.blade.php ENDPATH**/ ?>