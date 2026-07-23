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
    <dl class="mb-4 grid grid-cols-2 gap-3 text-sm">
        <div><dt class="text-slate-500"><?php echo e(__('Vendor')); ?></dt><dd><?php echo e($tabData['vendor']?->vendor_name ?? '—'); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Purchase Order')); ?></dt><dd><?php echo e($tabData['purchase_order']?->po_number ?? '—'); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Goods Receipt')); ?></dt><dd><?php echo e($tabData['goods_receipt']?->receipt_number ?? '—'); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Capitalization')); ?></dt><dd><?php echo e($tabData['capitalization']?->candidate_number ?? '—'); ?></dd></div>
    </dl>
    <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Acquisition Timeline')); ?></h3>
    <ul class="space-y-2 text-sm">
        <?php $__empty_1 = true; $__currentLoopData = $tabData['timeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="flex justify-between border-b border-erp-border pb-2">
                <span><?php echo e($event['event']); ?> — <?php echo e($event['ref']); ?></span>
                <span class="text-slate-500"><?php echo e(optional($event['date'])->format('Y-m-d')); ?></span>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="text-slate-500"><?php echo e(__('No procurement history.')); ?></li>
        <?php endif; ?>
    </ul>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\360\tabs\procurement.blade.php ENDPATH**/ ?>