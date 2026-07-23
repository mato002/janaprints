<?php
    $approvals = $dashboard['approvals'] ?? null;
?>

<?php if(! empty($approvals['visible'])): ?>
    <section class="exec-panel exec-panel--approvals" aria-label="<?php echo e(__('Executive approval queue')); ?>">
        <div class="exec-panel__head">
            <h2 class="exec-panel__title"><?php echo e(__('Executive Approval Queue')); ?></h2>
            <?php if(! empty($approvals['queue_url'])): ?>
                <a href="<?php echo e($approvals['queue_url']); ?>" data-turbo-frame="erp-main" class="exec-panel__meta exec-panel__meta--link">
                    <?php echo e(__('View full queue')); ?>

                </a>
            <?php endif; ?>
        </div>

        <div class="exec-approval-summary mb-3 grid grid-cols-3 gap-2">
            <div class="exec-approval-summary__item">
                <span class="exec-approval-summary__label"><?php echo e(__('Waiting')); ?></span>
                <span class="exec-approval-summary__value"><?php echo e($approvals['summary']['waiting']); ?></span>
            </div>
            <div class="exec-approval-summary__item exec-approval-summary__item--critical">
                <span class="exec-approval-summary__label"><?php echo e(__('Critical')); ?></span>
                <span class="exec-approval-summary__value"><?php echo e($approvals['summary']['critical']); ?></span>
            </div>
            <div class="exec-approval-summary__item exec-approval-summary__item--aging">
                <span class="exec-approval-summary__label"><?php echo e(__('Aging')); ?></span>
                <span class="exec-approval-summary__value"><?php echo e($approvals['summary']['aging']); ?></span>
            </div>
        </div>

        <?php echo $__env->make('admin.executive.approvals.partials.table', [
            'rows' => collect($approvals['items'])->take(8),
            'canAction' => $approvals['can_action'],
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\approval-queue.blade.php ENDPATH**/ ?>