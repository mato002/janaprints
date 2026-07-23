<?php
    $summaryMetrics = [
        ['label' => __('Active Conversations'), 'value' => $totals['active'], 'tone' => 'default'],
        ['label' => __('Escalated Threads'), 'value' => $totals['escalated'], 'tone' => $totals['escalated'] > 0 ? 'danger' : 'muted'],
        ['label' => __('Unassigned Threads'), 'value' => $totals['unassigned'], 'tone' => $totals['unassigned'] > 0 ? 'warning' : 'muted'],
        ['label' => __('Avg First Response'), 'value' => $teamAvgFirstResponse, 'tone' => 'default'],
        ['label' => __('Avg Resolution Time'), 'value' => $teamAvgResolution, 'tone' => 'default'],
    ];
?>

<section class="exec-panel exec-team-cc__summary" aria-label="<?php echo e(__('Team operations summary')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Team Operations Summary')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__(':members team members', ['members' => $teamMembers->count()])); ?></span>
    </div>
    <div class="exec-team-cc__summary-grid">
        <?php $__currentLoopData = $summaryMetrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $valueClass = match ($metric['tone']) {
                    'danger' => 'text-red-600',
                    'warning' => 'text-amber-600',
                    default => 'text-erp-primary',
                };
            ?>
            <div class="exec-team-cc__summary-metric">
                <span class="exec-team-cc__summary-label"><?php echo e($metric['label']); ?></span>
                <span class="exec-team-cc__summary-value <?php echo e($valueClass); ?>"><?php echo e($metric['value']); ?></span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\team\partials\summary.blade.php ENDPATH**/ ?>