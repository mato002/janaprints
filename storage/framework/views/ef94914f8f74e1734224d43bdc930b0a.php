<section class="comm-log-360__card">
    <h2 class="comm-log-360__card-title"><?php echo e(__('Delivery timeline')); ?></h2>
    <ul class="comm-log-360__timeline" role="list">
        <li class="comm-log-360__timeline-item">
            <span class="comm-log-360__timeline-dot comm-log-360__timeline-dot--default" aria-hidden="true"></span>
            <div class="comm-log-360__timeline-body">
                <p class="comm-log-360__timeline-title"><?php echo e(__('Created')); ?></p>
                <p class="comm-log-360__timeline-meta"><?php echo e($log->created_at?->format('d M Y • H:i')); ?></p>
            </div>
        </li>
        <?php $__empty_1 = true; $__currentLoopData = $timelineEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $eventTone = str_contains(strtolower((string) $event->event), 'fail')
                    ? 'danger'
                    : (str_contains(strtolower((string) $event->event), 'deliver') ? 'success' : 'default');
            ?>
            <li class="comm-log-360__timeline-item">
                <span class="comm-log-360__timeline-dot comm-log-360__timeline-dot--<?php echo e($eventTone); ?>" aria-hidden="true"></span>
                <div class="comm-log-360__timeline-body">
                    <p class="comm-log-360__timeline-title"><?php echo e(ucfirst(str_replace('_', ' ', $event->event))); ?></p>
                    <?php if($event->status_snapshot): ?>
                        <p class="comm-log-360__timeline-sub"><?php echo e($event->status_snapshot); ?></p>
                    <?php endif; ?>
                    <p class="comm-log-360__timeline-meta"><?php echo e($event->created_at?->format('d M Y • H:i')); ?></p>
                </div>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if($log->sent_at): ?>
                <li class="comm-log-360__timeline-item">
                    <span class="comm-log-360__timeline-dot comm-log-360__timeline-dot--success" aria-hidden="true"></span>
                    <div class="comm-log-360__timeline-body">
                        <p class="comm-log-360__timeline-title"><?php echo e(__('Sent')); ?></p>
                        <p class="comm-log-360__timeline-meta"><?php echo e($log->sent_at->format('d M Y • H:i')); ?></p>
                    </div>
                </li>
            <?php endif; ?>
            <?php if($log->delivered_at): ?>
                <li class="comm-log-360__timeline-item">
                    <span class="comm-log-360__timeline-dot comm-log-360__timeline-dot--success" aria-hidden="true"></span>
                    <div class="comm-log-360__timeline-body">
                        <p class="comm-log-360__timeline-title"><?php echo e(__('Delivered')); ?></p>
                        <p class="comm-log-360__timeline-meta"><?php echo e($log->delivered_at->format('d M Y • H:i')); ?></p>
                    </div>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\360\tab-timeline.blade.php ENDPATH**/ ?>