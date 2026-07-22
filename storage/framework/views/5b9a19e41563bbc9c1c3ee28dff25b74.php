<section class="exec-panel exec-panel--insights exec-team-cc__insights" aria-label="<?php echo e(__('Team insights')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Team Insights')); ?></h2>
    </div>

    <dl class="exec-team-cc__insights-list">
        <div class="exec-team-cc__insight-row">
            <dt><?php echo e(__('Most active user')); ?></dt>
            <dd><?php echo e($mostActive['user'] ?? __('—')); ?></dd>
        </div>
        <div class="exec-team-cc__insight-row">
            <dt><?php echo e(__('Fastest responder')); ?></dt>
            <dd>
                <?php if(($fastestResponder['avg_response_minutes'] ?? null) !== null): ?>
                    <?php echo e($fastestResponder['user']); ?> · <?php echo e($fastestResponder['avg_response_minutes']); ?>m
                <?php elseif($fastestResponder): ?>
                    <?php echo e($fastestResponder['user']); ?> · <?php echo e(__('Lowest escalation')); ?> (<?php echo e($fastestResponder['escalation_rate']); ?>%)
                <?php else: ?>
                    <?php echo e(__('—')); ?>

                <?php endif; ?>
            </dd>
        </div>
        <div class="exec-team-cc__insight-row">
            <dt><?php echo e(__('Highest resolution rate')); ?></dt>
            <dd>
                <?php if(($highestResolution['avg_resolution_minutes'] ?? null) !== null): ?>
                    <?php echo e($highestResolution['user']); ?> · <?php echo e($highestResolution['avg_resolution_minutes']); ?>m
                <?php elseif($highestResolution): ?>
                    <?php echo e($highestResolution['user']); ?> · <?php echo e(max(0, 100 - $highestResolution['escalation_rate'])); ?>%
                <?php else: ?>
                    <?php echo e(__('—')); ?>

                <?php endif; ?>
            </dd>
        </div>
        <div class="exec-team-cc__insight-row">
            <dt><?php echo e(__('Most escalations')); ?></dt>
            <dd>
                <?php if($hasEscalationSignal): ?>
                    <?php echo e($mostEscalations['user']); ?> · <?php echo e($mostEscalations['escalation_rate']); ?>%
                <?php else: ?>
                    <?php echo e(__('None')); ?>

                <?php endif; ?>
            </dd>
        </div>
        <div class="exec-team-cc__insight-row exec-team-cc__insight-row--highlight">
            <dt><?php echo e(__('Team utilization')); ?></dt>
            <dd class="tabular-nums"><?php echo e($teamUtilization); ?>%</dd>
        </div>
    </dl>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\team\partials\insights.blade.php ENDPATH**/ ?>