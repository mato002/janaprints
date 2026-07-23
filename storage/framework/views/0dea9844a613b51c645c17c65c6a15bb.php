<section class="space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $communications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="ess-card">
            <div class="flex flex-col gap-1">
                <p class="font-semibold"><?php echo e($message['subject'] ?? __('Notification')); ?></p>
                <p class="text-xs text-erp-muted">
                    <?php echo e($message['channel']); ?> · <?php echo e($message['status']); ?> · <?php echo e($message['sent_at']?->format('d M Y H:i')); ?>

                </p>
                <?php if(! empty($message['preview'])): ?>
                    <p class="mt-2 text-sm text-erp-muted line-clamp-3"><?php echo e(strip_tags($message['preview'])); ?></p>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="ess-card text-sm text-erp-muted"><?php echo e(__('No communications yet.')); ?></div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\tabs\communications.blade.php ENDPATH**/ ?>