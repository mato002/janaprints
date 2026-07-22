<div class="comm-log-360__analytics-grid">
    <div class="comm-log-360__analytics-card comm-log-360__analytics-card--rate">
        <span class="comm-log-360__analytics-label"><?php echo e(__('Delivery rate')); ?></span>
        <span class="comm-log-360__analytics-value"><?php echo e($deliveryRateLabel); ?></span>
        <span class="comm-log-360__analytics-hint"><?php echo e(__('Based on current status')); ?></span>
    </div>
    <div class="comm-log-360__analytics-card comm-log-360__analytics-card--failures">
        <span class="comm-log-360__analytics-label"><?php echo e(__('Failures')); ?></span>
        <span class="comm-log-360__analytics-value"><?php echo e($failureCount); ?></span>
        <span class="comm-log-360__analytics-hint"><?php echo e(__('Delivery events flagged failed')); ?></span>
    </div>
    <div class="comm-log-360__analytics-card">
        <span class="comm-log-360__analytics-label"><?php echo e(__('Recipients')); ?></span>
        <span class="comm-log-360__analytics-value"><?php echo e($recipientCount); ?></span>
        <span class="comm-log-360__analytics-hint"><?php echo e(__('Total audience')); ?></span>
    </div>
    <div class="comm-log-360__analytics-card">
        <span class="comm-log-360__analytics-label"><?php echo e(__('Response')); ?></span>
        <span class="comm-log-360__analytics-value comm-log-360__analytics-value--sm"><?php echo e($responseLabel); ?></span>
        <span class="comm-log-360__analytics-hint"><?php echo e(__('Read receipt / engagement')); ?></span>
    </div>
</div>

<section class="comm-log-360__card mt-4">
    <h2 class="comm-log-360__card-title"><?php echo e(__('Delivery insights')); ?></h2>
    <p class="text-sm text-slate-600">
        <?php echo e(__('This panel is ready for extended analytics — channel benchmarks, cohort comparisons, and response funnels can plug in here without changing the communication log record.')); ?>

    </p>
    <dl class="comm-log-360__dl mt-4">
        <div>
            <dt><?php echo e(__('Events logged')); ?></dt>
            <dd><?php echo e($eventCount); ?></dd>
        </div>
        <div>
            <dt><?php echo e(__('Channel')); ?></dt>
            <dd><?php echo e($log->channel->label()); ?></dd>
        </div>
        <div>
            <dt><?php echo e(__('Final status')); ?></dt>
            <dd><?php echo e($log->status->label()); ?></dd>
        </div>
    </dl>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\360\tab-analytics.blade.php ENDPATH**/ ?>