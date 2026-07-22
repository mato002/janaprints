<div class="comm-log-360__recipient-grid">
    <?php $__empty_1 = true; $__currentLoopData = $log->recipients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recipient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="comm-log-360__recipient-card">
            <div class="comm-log-360__recipient-head">
                <h3 class="comm-log-360__recipient-name"><?php echo e($recipient->display_name ?: __('Recipient')); ?></h3>
                <?php if($recipient->delivery_status): ?>
                    <span class="comm-log-360__badge <?php echo e($recipient->delivery_status->badgeClass()); ?>">
                        <?php echo e($recipient->delivery_status->label()); ?>

                    </span>
                <?php endif; ?>
            </div>
            <dl class="comm-log-360__recipient-meta">
                <?php if($recipient->phone): ?>
                    <div>
                        <dt><?php echo e(__('Phone')); ?></dt>
                        <dd><?php echo e($recipient->phone); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if($recipient->email): ?>
                    <div>
                        <dt><?php echo e(__('Email')); ?></dt>
                        <dd><a href="mailto:<?php echo e($recipient->email); ?>" class="comm-log-360__link"><?php echo e($recipient->email); ?></a></dd>
                    </div>
                <?php endif; ?>
                <?php if($recipient->read_at): ?>
                    <div>
                        <dt><?php echo e(__('Read at')); ?></dt>
                        <dd><?php echo e($recipient->read_at->format('d M Y H:i')); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="comm-log-360__empty"><?php echo e(__('No recipients recorded for this communication.')); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\360\tab-recipients.blade.php ENDPATH**/ ?>