<section class="comm-log-360__card">
    <h2 class="comm-log-360__card-title"><?php echo e(__('Activity ledger')); ?></h2>
    <ul class="comm-log-360__ledger" role="list">
        <?php $__empty_1 = true; $__currentLoopData = $auditEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="comm-log-360__ledger-row">
                <div class="comm-log-360__ledger-action"><?php echo e($entry['action']); ?></div>
                <div class="comm-log-360__ledger-user"><?php echo e($entry['user']); ?></div>
                <time class="comm-log-360__ledger-time"><?php echo e($entry['at']?->format('d M Y • H:i')); ?></time>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="comm-log-360__empty"><?php echo e(__('No audit activity recorded.')); ?></li>
        <?php endif; ?>
    </ul>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\360\tab-audit.blade.php ENDPATH**/ ?>