<section class="exec-panel exec-inbox-cc__section-panel" aria-label="<?php echo e(__('Team workload')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Team Workload')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__(':count unassigned', ['count' => $stats['unassigned']])); ?></span>
    </div>

    <div class="exec-inbox-cc__workload-split">
        <div>
            <h3 class="exec-inbox-cc__subhead"><?php echo e(__('Conversations per user')); ?></h3>
            <?php if($assigneeLoads->isEmpty()): ?>
                <p class="exec-inbox-cc__thread-empty"><?php echo e(__('No assignee load in the current snapshot.')); ?></p>
            <?php else: ?>
                <ul class="exec-inbox-cc__workload-bars" role="list">
                    <?php $maxLoad = max(1, (int) $assigneeLoads->max('count')); ?>
                    <?php $__currentLoopData = $assigneeLoads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $pct = (int) round(($row['count'] / $maxLoad) * 100); ?>
                        <li class="exec-inbox-cc__workload-bar-row">
                            <span class="exec-inbox-cc__workload-name"><?php echo e($row['name']); ?></span>
                            <div class="exec-progress__track exec-inbox-cc__workload-track">
                                <div class="exec-progress__bar" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                            <span class="exec-inbox-cc__workload-count"><?php echo e($row['count']); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>

        <div>
            <h3 class="exec-inbox-cc__subhead"><?php echo e(__('Unassigned conversations')); ?></h3>
            <ul class="exec-inbox-cc__thread-list" role="list">
                <?php $__empty_1 = true; $__currentLoopData = $stats['recent_unassigned']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li>
                        <a href="<?php echo e(route('admin.communications.inbox.index', ['conversation' => $conv->id])); ?>" class="exec-inbox-cc__thread-row" data-turbo-frame="erp-main">
                            <span class="exec-inbox-cc__thread-name"><?php echo e($conv->display_name ?? $conv->conversation_code); ?></span>
                            <span class="exec-inbox-cc__thread-meta"><?php echo e($conv->last_activity_at?->diffForHumans()); ?></span>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="exec-inbox-cc__thread-empty"><?php echo e(__('All active threads are assigned.')); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <?php if($stats['most_active_customers']->isNotEmpty()): ?>
        <details class="exec-intelligence mt-3">
            <summary class="exec-intelligence__summary">
                <?php echo e(__('Most active customers')); ?>

                <span class="exec-intelligence__hint"><?php echo e(__('Thread volume')); ?></span>
            </summary>
            <div class="exec-intelligence__body">
                <ul class="exec-inbox-cc__thread-list" role="list">
                    <?php $__currentLoopData = $stats['most_active_customers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="exec-inbox-cc__thread-row exec-inbox-cc__thread-row--static">
                            <span class="exec-inbox-cc__thread-name"><?php echo e($row->customer?->company_name ?? __('Unknown')); ?></span>
                            <span class="exec-inbox-cc__thread-meta"><?php echo e($row->thread_count); ?> <?php echo e(__('threads')); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </details>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\executive\partials\team-workload.blade.php ENDPATH**/ ?>