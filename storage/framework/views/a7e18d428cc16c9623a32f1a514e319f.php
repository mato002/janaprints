<section class="comm-log-360__kpi-strip" aria-label="<?php echo e(__('Communication KPIs')); ?>">
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label"><?php echo e(__('Message status')); ?></span>
        <span class="comm-log-360__kpi-value"><?php echo e($log->status->label()); ?></span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label"><?php echo e(__('Recipients')); ?></span>
        <span class="comm-log-360__kpi-value"><?php echo e($recipientCount); ?></span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label"><?php echo e(__('Delivery events')); ?></span>
        <span class="comm-log-360__kpi-value"><?php echo e($eventCount); ?></span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label"><?php echo e(__('Channel')); ?></span>
        <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm"><?php echo e($log->channel->label()); ?></span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label"><?php echo e(__('Created by')); ?></span>
        <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm"><?php echo e($log->creator?->name ?? '—'); ?></span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label"><?php echo e(__('Sent time')); ?></span>
        <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm"><?php echo e($log->sent_at?->format('d M H:i') ?? '—'); ?></span>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\360\kpi-strip.blade.php ENDPATH**/ ?>