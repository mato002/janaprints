<?php
    $panel = $walkInPanel ?? ['mode' => 'customer', 'title' => __('Context')];
    $mode = $panel['mode'] ?? 'customer';
?>

<aside class="space-y-3">
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
        <div class="mb-3 flex items-start justify-between gap-2">
            <h3 class="text-sm font-semibold text-slate-900"><?php echo e($panel['title'] ?? __('Context')); ?></h3>
            <?php if(! empty($panel['customer_360_url'])): ?>
                <a href="<?php echo e($panel['customer_360_url']); ?>" class="shrink-0 text-xs font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main"><?php echo e(__('Customer 360')); ?></a>
            <?php endif; ?>
        </div>

        <?php if($mode === 'customer'): ?>
            <?php echo $__env->make('admin.sales.desk.partials.walk-in-panel.customer', ['panel' => $panel], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($mode === 'specification'): ?>
            <?php echo $__env->make('admin.sales.desk.partials.walk-in-panel.specification', ['panel' => $panel], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($mode === 'order'): ?>
            <?php echo $__env->make('admin.sales.desk.partials.walk-in-panel.order', ['panel' => $panel], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('admin.sales.desk.partials.walk-in-panel.release', ['panel' => $panel], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
</aside>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/partials/walk-in-panel.blade.php ENDPATH**/ ?>