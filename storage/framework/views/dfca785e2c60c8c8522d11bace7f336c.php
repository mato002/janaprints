<?php if($panel['customer_name'] ?? null): ?>
    <p class="mb-3 text-xs text-slate-600">
        <?php echo e(__('Customer')); ?>:
        <span class="font-medium text-slate-900"><?php echo e($panel['customer_name']); ?></span>
    </p>
<?php endif; ?>

<?php if($panel['selected'] ?? null): ?>
    <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50/60 px-3 py-2 text-sm">
        <p class="text-xs font-medium uppercase tracking-wide text-emerald-800"><?php echo e(__('Selected specification')); ?></p>
        <p class="mt-1 font-medium text-slate-900"><?php echo e($panel['selected']['name']); ?></p>
        <p class="text-xs text-slate-600">
            <?php echo e($panel['selected']['code']); ?>

            <?php if($panel['selected']['product'] ?? null): ?>
                · <?php echo e($panel['selected']['product']); ?>

            <?php endif; ?>
        </p>
        <p class="mt-1 text-xs">
            <?php if($panel['selected']['has_artwork'] ?? false): ?>
                <span class="text-emerald-700">✓ <?php echo e(__('Artwork')); ?>: <?php echo e($panel['selected']['artwork_label']); ?></span>
            <?php else: ?>
                <span class="text-amber-700">⚠ <?php echo e(__('Artwork pending')); ?></span>
            <?php endif; ?>
        </p>
        <?php if($panel['selected']['default_unit_price'] ?? null): ?>
            <p class="mt-1 text-xs text-slate-600"><?php echo e(__('Default price')); ?>: <?php echo e($panel['selected']['default_unit_price']); ?></p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p class="mb-3 text-sm text-slate-600">
        <?php echo e(__(':count saved specification(s). Select one or create new to continue.', ['count' => $panel['saved_count'] ?? 0])); ?>

    </p>
<?php endif; ?>

<?php if(count($panel['recent'] ?? []) > 0): ?>
    <div class="border-t border-erp-border pt-3">
        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Saved specifications')); ?></p>
        <ul class="space-y-2 text-xs">
            <?php $__currentLoopData = $panel['recent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="rounded border border-slate-100 bg-slate-50/80 px-2 py-1.5">
                    <p class="font-medium text-slate-900"><?php echo e($spec['name']); ?></p>
                    <p class="text-slate-500">
                        <?php echo e($spec['code']); ?>

                        <?php if($spec['product'] ?? null): ?>
                            · <?php echo e($spec['product']); ?>

                        <?php endif; ?>
                    </p>
                    <p class="mt-0.5">
                        <?php if($spec['has_artwork'] ?? false): ?>
                            <span class="text-emerald-700"><?php echo e($spec['artwork']); ?></span>
                        <?php else: ?>
                            <span class="text-amber-700"><?php echo e(__('No artwork')); ?></span>
                        <?php endif; ?>
                        <?php if($spec['price'] ?? null): ?>
                            <span class="text-slate-500"> · <?php echo e($spec['price']); ?></span>
                        <?php endif; ?>
                    </p>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/partials/walk-in-panel/specification.blade.php ENDPATH**/ ?>