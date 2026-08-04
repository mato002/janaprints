<?php if(! empty($summary)): ?>
    <?php $compact = (bool) ($compact ?? false); ?>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'production-queue-summary',
        'production-queue-summary--compact' => $compact,
        'mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 rounded-lg border border-erp-border bg-white px-4 py-3 text-sm text-slate-600' => ! $compact,
    ]); ?>">
        <span><?php echo e(__('Visible jobs')); ?>: <?php echo e($summary['total_visible'] ?? 0); ?></span>
        <span class="production-queue-summary__sep" aria-hidden="true">·</span>
        <span><?php echo e(__('Waiting')); ?>: <?php echo e($summary['waiting'] ?? 0); ?></span>
        <span class="production-queue-summary__sep" aria-hidden="true">·</span>
        <span><?php echo e(__('Running')); ?>: <?php echo e($summary['running'] ?? 0); ?></span>
        <span class="production-queue-summary__sep" aria-hidden="true">·</span>
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-medium text-red-700' => (int) ($summary['overdue'] ?? 0) > 0]); ?>">
            <?php echo e(__('Overdue')); ?>: <?php echo e($summary['overdue'] ?? 0); ?>

        </span>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\summary.blade.php ENDPATH**/ ?>