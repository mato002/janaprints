<?php $inv = $dashboard['inventory']; ?>
<section class="exec-panel">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Inventory Health')); ?></h2>
        <span class="text-[11px] font-medium text-erp-primary"><?php echo e($inv['inventory_value']); ?></span>
    </div>
    <div class="grid grid-cols-2 gap-2 text-[11px]">
        <div>
            <p class="mb-1 font-semibold text-slate-600"><?php echo e(__('Low stock')); ?> (<?php echo e(count($inv['low_stock'])); ?>)</p>
            <ul class="space-y-0.5 text-slate-600">
                <?php $__empty_1 = true; $__currentLoopData = $inv['low_stock']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li><?php echo e($item['name']); ?> <span class="text-slate-400">(<?php echo e($item['sku']); ?>)</span></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-slate-400">—</li>
                <?php endif; ?>
            </ul>
        </div>
        <div>
            <p class="mb-1 font-semibold text-slate-600"><?php echo e(__('Out of stock')); ?> (<?php echo e(count($inv['out_of_stock'])); ?>)</p>
            <ul class="space-y-0.5 text-slate-600">
                <?php $__empty_1 = true; $__currentLoopData = $inv['out_of_stock']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li><?php echo e($item['name']); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-slate-400">—</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="col-span-2">
            <p class="mb-1 font-semibold text-slate-600"><?php echo e(__('Fast moving')); ?></p>
            <ul class="flex flex-wrap gap-x-3 gap-y-0.5 text-slate-600">
                <?php $__empty_1 = true; $__currentLoopData = $inv['fast_moving']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li><?php echo e($item['name']); ?> <span class="text-slate-400"><?php echo e(number_format($item['issued'], 0)); ?></span></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-slate-400">—</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <p class="mt-2 text-[10px] text-slate-500"><?php echo e(__('Reorder alerts')); ?>: <strong><?php echo e($inv['reorder_alerts']); ?></strong></p>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/dashboard/partials/inventory-health.blade.php ENDPATH**/ ?>