<?php if(count($panel['warnings'] ?? []) > 0): ?>
    <ul class="mb-3 space-y-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs">
        <?php $__currentLoopData = $panel['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="flex items-start gap-1.5 text-amber-900">
                <span aria-hidden="true">⚠</span>
                <span><?php echo e($warning['message']); ?></span>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
<?php endif; ?>

<dl class="space-y-2 text-sm">
    <div>
        <dt class="text-xs text-slate-500"><?php echo e(__('Customer')); ?></dt>
        <dd class="font-medium text-slate-900"><?php echo e($panel['customer_name'] ?? '—'); ?></dd>
    </div>
    <div>
        <dt class="text-xs text-slate-500"><?php echo e(__('Specification')); ?></dt>
        <dd class="font-medium text-slate-900"><?php echo e($panel['specification_name'] ?? '—'); ?></dd>
    </div>
    <div>
        <dt class="text-xs text-slate-500"><?php echo e(__('Product')); ?></dt>
        <dd class="font-medium text-slate-900"><?php echo e($panel['product'] ?? '—'); ?></dd>
    </div>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <dt class="text-xs text-slate-500"><?php echo e(__('Default qty')); ?></dt>
            <dd class="font-mono text-slate-900"><?php echo e($panel['default_quantity'] ?? '—'); ?></dd>
        </div>
        <div>
            <dt class="text-xs text-slate-500"><?php echo e(__('Default price')); ?></dt>
            <dd class="font-mono text-slate-900"><?php echo e($panel['default_unit_price'] ?? '—'); ?></dd>
        </div>
    </div>
    <div>
        <dt class="text-xs text-slate-500"><?php echo e(__('Artwork')); ?></dt>
        <dd class="font-medium">
            <?php if($panel['has_artwork'] ?? false): ?>
                <span class="text-emerald-700">✓ <?php echo e($panel['artwork_label']); ?></span>
            <?php else: ?>
                <span class="text-amber-700">⚠ <?php echo e(__('Pending')); ?></span>
            <?php endif; ?>
        </dd>
    </div>
    <?php if($panel['outstanding_balance'] ?? null): ?>
        <div>
            <dt class="text-xs text-slate-500"><?php echo e(__('Outstanding balance')); ?></dt>
            <dd class="font-mono text-amber-800"><?php echo e($panel['outstanding_balance']); ?></dd>
        </div>
    <?php endif; ?>
</dl>

<p class="mt-3 text-xs text-slate-500"><?php echo e(__('Quantity and price on the form are for this order. Change them only when this sale differs from the specification defaults.')); ?></p>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\partials\walk-in-panel\order.blade.php ENDPATH**/ ?>