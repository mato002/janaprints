<?php
    $firstResponse = $stats['avg_first_response_minutes'] !== null
        ? $stats['avg_first_response_minutes'].'m'
        : '—';
?>

<section class="exec-panel exec-inbox-cc__section-panel" aria-label="<?php echo e(__('Response performance')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Response Performance')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__(':closed closed today', ['closed' => $stats['closed_today']])); ?></span>
    </div>
    <div class="exec-metric-grid exec-metric-grid--2 sm:grid-cols-4">
        <div class="exec-metric exec-inbox-cc__metric-tile">
            <span class="exec-metric__label"><?php echo e(__('Avg First Response')); ?></span>
            <span class="exec-metric__value"><?php echo e($firstResponse); ?></span>
        </div>
        <div class="exec-metric exec-inbox-cc__metric-tile">
            <span class="exec-metric__label"><?php echo e(__('Avg Resolution Time')); ?></span>
            <span class="exec-metric__value text-slate-400">—</span>
            <span class="exec-inbox-cc__metric-foot"><?php echo e(__('Not tracked on this view')); ?></span>
        </div>
        <div class="exec-metric exec-inbox-cc__metric-tile">
            <span class="exec-metric__label"><?php echo e(__('Customer Satisfaction')); ?></span>
            <span class="exec-metric__value text-slate-400">—</span>
            <span class="exec-inbox-cc__metric-foot"><?php echo e(__('Survey data pending')); ?></span>
        </div>
        <div class="exec-metric exec-inbox-cc__metric-tile">
            <span class="exec-metric__label"><?php echo e(__('Volume Today')); ?></span>
            <span class="exec-metric__value text-erp-accent"><?php echo e($stats['volume_today']); ?></span>
            <span class="exec-inbox-cc__metric-foot"><?php echo e(__(':unanswered unanswered', ['unanswered' => $stats['unanswered']])); ?></span>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\executive\partials\performance-panel.blade.php ENDPATH**/ ?>