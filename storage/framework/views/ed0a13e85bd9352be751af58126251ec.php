<ul class="space-y-2 text-sm">
    <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <li class="flex justify-between border-b border-erp-border pb-2">
            <span><?php echo e($entry->title ?? $entry['title'] ?? ''); ?></span>
            <span class="text-slate-500"><?php echo e(($entry->occurred_at ?? $entry['occurred_at'] ?? null)?->format('Y-m-d H:i')); ?></span>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="text-slate-500"><?php echo e(__('No timeline entries.')); ?></li>
    <?php endif; ?>
</ul>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\360\partials\timeline.blade.php ENDPATH**/ ?>