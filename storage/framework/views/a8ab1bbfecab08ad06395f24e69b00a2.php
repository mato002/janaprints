<?php
    $healthMetrics = [
        ['label' => __('Open Threads'), 'value' => $stats['open'], 'tone' => $stats['open'] > 0 ? 'default' : 'muted'],
        ['label' => __('Waiting Customer'), 'value' => $stats['waiting_customer'], 'tone' => $stats['waiting_customer'] > 0 ? 'warning' : 'muted'],
        ['label' => __('Waiting Team'), 'value' => $stats['waiting_internal'], 'tone' => $stats['waiting_internal'] > 0 ? 'warning' : 'muted'],
        ['label' => __('SLA Breaches'), 'value' => $stats['overdue'], 'tone' => $stats['overdue'] > 0 ? 'danger' : 'success'],
        ['label' => __('Escalations'), 'value' => $stats['escalated'], 'tone' => $stats['escalated'] > 0 ? 'danger' : 'muted'],
    ];
?>

<section class="exec-panel exec-inbox-cc__section-panel" aria-label="<?php echo e(__('Communication health')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Communication Health')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__(':active active · :unread unread', ['active' => $stats['active'], 'unread' => $stats['unread_total']])); ?></span>
    </div>
    <div class="exec-metric-grid exec-metric-grid--5">
        <?php $__currentLoopData = $healthMetrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $valueClass = match ($metric['tone']) {
                    'danger' => 'text-red-600',
                    'warning' => 'text-amber-600',
                    'success' => 'text-emerald-600',
                    default => 'text-erp-primary',
                };
            ?>
            <div class="exec-metric exec-inbox-cc__metric-tile">
                <span class="exec-metric__label"><?php echo e($metric['label']); ?></span>
                <span class="exec-metric__value <?php echo e($valueClass); ?>"><?php echo e($metric['value']); ?></span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\executive\partials\health-panel.blade.php ENDPATH**/ ?>