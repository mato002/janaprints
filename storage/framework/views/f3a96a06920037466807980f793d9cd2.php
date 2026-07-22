<?php $events = $tabData['events'] ?? []; ?>

<div class="space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="flex flex-col gap-1 border-l-2 border-erp-border pl-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-900">
                    <?php echo e($event['label']); ?>

                    <?php if(! empty($event['is_current'])): ?>
                        <span class="erp-badge ml-1"><?php echo e(__('Current')); ?></span>
                    <?php endif; ?>
                </p>
                <?php if(! empty($event['detail'])): ?>
                    <p class="text-sm text-slate-600"><?php echo e($event['detail']); ?></p>
                <?php endif; ?>
            </div>
            <div class="text-xs text-slate-500 sm:text-right">
                <p><?php echo e($event['at'] ? \Illuminate\Support\Carbon::parse($event['at'])->format('Y-m-d H:i') : '—'); ?></p>
                <?php if(! empty($event['user'])): ?>
                    <p><?php echo e($event['user']); ?></p>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-sm text-slate-500"><?php echo e(__('No timeline events yet.')); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\workspace\tabs\timeline.blade.php ENDPATH**/ ?>