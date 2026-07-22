<?php
    $attentionColumns = [
        [
            'title' => __('Priority conversations'),
            'badge' => $priorityThreads->count(),
            'tone' => 'critical',
            'items' => $priorityThreads,
            'empty' => __('No VIP or high-value threads need attention.'),
        ],
        [
            'title' => __('Waiting customers'),
            'badge' => $stats['longest_waiting']->count(),
            'tone' => 'warning',
            'items' => $stats['longest_waiting'],
            'empty' => __('No customers are waiting on a reply.'),
            'hint' => fn ($conv) => $conv->waiting_since?->diffForHumans(),
        ],
        [
            'title' => __('Overdue SLA'),
            'badge' => $stats['overdue'],
            'tone' => 'critical',
            'items' => $overdueThreads,
            'empty' => __('All SLAs are within target.'),
            'hint' => fn ($conv) => $conv->sla_status?->label(),
        ],
        [
            'title' => __('Escalated threads'),
            'badge' => $stats['escalated'],
            'tone' => 'critical',
            'items' => $stats['recent_escalated'],
            'empty' => __('No escalated threads right now.'),
            'hint' => fn ($conv) => $conv->escalated_at?->diffForHumans(),
        ],
    ];
?>

<section class="exec-panel exec-panel--attention exec-inbox-cc__attention" aria-label="<?php echo e(__('Attention center')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Attention Center')); ?></h2>
        <span class="exec-attention-ribbon"><?php echo e(__('Needs review')); ?></span>
    </div>

    <div class="exec-inbox-cc__attention-grid">
        <?php $__currentLoopData = $attentionColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="exec-inbox-cc__attention-col exec-inbox-cc__attention-col--<?php echo e($column['tone']); ?>">
                <div class="exec-inbox-cc__attention-col-head">
                    <h3 class="exec-inbox-cc__attention-col-title"><?php echo e($column['title']); ?></h3>
                    <span class="exec-badge exec-badge--<?php echo e($column['tone'] === 'critical' ? 'danger' : 'warning'); ?>"><?php echo e($column['badge']); ?></span>
                </div>
                <ul class="exec-inbox-cc__thread-list" role="list">
                    <?php $__empty_1 = true; $__currentLoopData = $column['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li>
                            <a href="<?php echo e(route('admin.communications.inbox.index', ['conversation' => $conv->id])); ?>" class="exec-inbox-cc__thread-row" data-turbo-frame="erp-main">
                                <span class="exec-inbox-cc__thread-name"><?php echo e($conv->display_name ?? $conv->conversation_code); ?></span>
                                <?php if(! empty($column['hint'])): ?>
                                    <span class="exec-inbox-cc__thread-meta"><?php echo e($column['hint']($conv)); ?></span>
                                <?php elseif($conv->assignee): ?>
                                    <span class="exec-inbox-cc__thread-meta"><?php echo e($conv->assignee->name); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="exec-inbox-cc__thread-empty"><?php echo e($column['empty']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\executive\partials\attention-center.blade.php ENDPATH**/ ?>