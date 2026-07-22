<section class="crm-360__card">
    <h2 class="crm-360__card-title"><?php echo e(__('Acquisition timeline')); ?></h2>
    <ul class="crm-360__feed" role="list">
        <?php $__empty_1 = true; $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="crm-360__feed-item">
                <div class="crm-360__feed-head">
                    <?php if($event['url']): ?>
                        <a href="<?php echo e($event['url']); ?>" class="crm-360__feed-title" data-turbo-frame="erp-main"><?php echo e($event['title']); ?></a>
                    <?php else: ?>
                        <span class="crm-360__feed-title"><?php echo e($event['title']); ?></span>
                    <?php endif; ?>
                    <span class="crm-360__pill"><?php echo e($event['badge']); ?></span>
                </div>
                <p class="crm-360__feed-meta"><?php echo e($event['body']); ?></p>
                <time class="crm-360__feed-time"><?php echo e($event['at']?->format('d M Y H:i')); ?></time>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="crm-360__empty-inline"><?php echo e(__('No timeline events yet')); ?></li>
        <?php endif; ?>
    </ul>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\tab-timeline.blade.php ENDPATH**/ ?>