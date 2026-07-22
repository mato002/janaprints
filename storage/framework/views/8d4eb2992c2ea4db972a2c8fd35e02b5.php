<section class="crm-360__card">
    <h2 class="crm-360__card-title"><?php echo e(__('Unified timeline')); ?></h2>
    <p class="mb-4 text-[11px] text-slate-500"><?php echo e(__('Quotes, orders, artwork, payments, communications, and activities in one ledger.')); ?></p>

    <ul class="crm-360__timeline" role="list">
        <?php $__empty_1 = true; $__currentLoopData = $unifiedTimeline->take(40); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $badgeClass = match ($event['kind']) {
                    'communication' => 'crm-360__badge--comm',
                    'payment' => 'crm-360__badge--payment',
                    'quote', 'order', 'artwork' => 'crm-360__badge--commercial',
                    'activity' => 'crm-360__badge--activity',
                    default => 'crm-360__badge--default',
                };
            ?>
            <li class="crm-360__timeline-item">
                <span class="crm-360__timeline-dot" aria-hidden="true"></span>
                <div class="crm-360__timeline-body">
                    <div class="crm-360__timeline-head">
                        <span class="crm-360__badge <?php echo e($badgeClass); ?>"><?php echo e($event['badge']); ?></span>
                        <time class="crm-360__timeline-date"><?php echo e($event['at']?->format('M j, Y')); ?></time>
                    </div>
                    <?php if($event['url']): ?>
                        <a href="<?php echo e($event['url']); ?>" class="crm-360__timeline-title" data-turbo-frame="erp-main"><?php echo e($event['title']); ?></a>
                    <?php else: ?>
                        <span class="crm-360__timeline-title"><?php echo e($event['title']); ?></span>
                    <?php endif; ?>
                    <p class="crm-360__timeline-meta"><?php echo e($event['body']); ?> · <?php echo e($event['at']?->diffForHumans()); ?></p>
                </div>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="crm-360__empty-inline py-8 text-center"><?php echo e(__('No timeline events yet')); ?></li>
        <?php endif; ?>
    </ul>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\tab-timeline.blade.php ENDPATH**/ ?>