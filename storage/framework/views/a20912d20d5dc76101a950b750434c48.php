<?php
    use App\Enums\ProductionJobCardStatus;
    use App\Support\Navigation\WorkspaceEmbed;

    $executionState = $executionState ?? [];
    $primaryAction = $primaryAction ?? null;
    $secondaryActions = $secondaryActions ?? [];
    $workflowPresentation = $workflowPresentation ?? null;
    $dispatchSummary = $dispatchSummary ?? null;

    $customerName = $header['customer_name'] ?? __('No customer');
    $qtyLabel = isset($header['quantity']) && (float) $header['quantity'] > 0
        ? number_format((float) $header['quantity'], 0)
        : '—';
    $dueLabel = $header['due_date']
        ? ($header['due_date']->isToday() ? __('Due today') : $header['due_date']->format('M j'))
        : '—';
    $progress = (int) ($header['progress_percent'] ?? 0);
    $stageLabel = $header['current_stage_label']
        ?? $executionState['stage_name']
        ?? $executionState['work_center']
        ?? '—';
    $statusLabel = $header['status']->label();

    $operatorName = $executionState['operator_name'] ?? null;
    $machineName = $executionState['machine_name'] ?? null;
    $needsOperator = (bool) ($executionState['needs_operator'] ?? false);
    $needsMachine = (bool) ($executionState['needs_machine'] ?? false);

    $dispatchSummaryState = $executionState['dispatch_summary'] ?? $dispatchSummary;
    $hasDispatch = ! empty($dispatchSummaryState['has_delivery_note']);
    $dispatchActions = $dispatchSummaryState['actions'] ?? ['primary' => null, 'secondary' => [], 'danger' => []];
    $workflowNextStep = $executionState['workflow_next_step'] ?? null;

    $heroAction = $hasDispatch
        ? ($dispatchActions['primary'] ?? null)
        : ($workflowNextStep
            ? ['label' => $workflowNextStep['label'], 'type' => 'link', 'url' => $workflowNextStep['url'], 'variant' => 'primary']
            : $primaryAction);

    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $formTurboAttrs = WorkspaceEmbed::mainFormAttributes();
?>

<header class="job-360-hero mes-header">
    <div class="job-360-hero__shell">
        <div class="job-360-hero__identity">
            <div class="job-360-hero__top-row">
                <span class="job-360-hero__job-number font-mono"><?php echo e($header['job_number']); ?></span>
                <?php if($header['is_delayed']): ?>
                    <span class="job-360-pill job-360-pill--danger"><?php echo e(__('Delayed')); ?></span>
                <?php endif; ?>
                <span class="job-360-pill job-360-pill--neutral"><?php echo e(str_replace('_', ' ', $header['priority']->value)); ?></span>
                <span class="job-360-pill job-360-pill--neutral"><?php echo e($statusLabel); ?></span>
            </div>

            <p class="job-360-hero__meta-line">
                <span class="font-semibold text-slate-800"><?php echo e($customerName); ?></span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span><?php echo e(__('Qty')); ?> <?php echo e($qtyLabel); ?></span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-red-700' => $header['is_delayed']]); ?>"><?php echo e(__('Due')); ?> <?php echo e($dueLabel); ?></span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span><?php echo e(__('Stage')); ?> <?php echo e($stageLabel); ?></span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-amber-800' => $needsOperator]); ?>"><?php echo e(__('Operator')); ?> <?php echo e($operatorName ?? '—'); ?></span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-amber-800' => $needsMachine]); ?>"><?php echo e(__('Machine')); ?> <?php echo e($machineName ?? '—'); ?></span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span class="job-360-hero__progress-inline">
                    <span class="job-360-hero__progress-track" aria-hidden="true">
                        <span class="job-360-hero__progress-fill" style="width: <?php echo e(min(100, max(0, $progress))); ?>%"></span>
                    </span>
                    <span class="job-360-hero__progress-value"><?php echo e($progress); ?>%</span>
                </span>
            </p>
        </div>

        <div class="job-360-hero__actions">
            <div class="job-360-hero__action-row">
                <?php if($hasDispatch): ?>
                    <?php if($dispatchActions['primary'] ?? null): ?>
                        <a href="<?php echo e($dispatchActions['primary']['url']); ?>" class="erp-btn-primary px-3 py-1.5 text-xs" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                            <?php echo e($dispatchActions['primary']['label']); ?>

                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if($needsOperator): ?>
                        <a href="#assign-operator" class="erp-btn-primary px-3 py-1.5 text-xs"><?php echo e(__('Assign operator')); ?></a>
                    <?php endif; ?>
                    <?php if($needsMachine): ?>
                        <a href="#assign-machine" class="erp-btn-primary px-3 py-1.5 text-xs"><?php echo e(__('Assign machine')); ?></a>
                    <?php endif; ?>
                    <?php if (! ($needsOperator || $needsMachine)): ?>
                        <?php echo $__env->make('admin.production.job-cards.workspace.partials.primary-action-button', [
                            'action' => $heroAction,
                            'completion' => $completion,
                            'size' => 'sm',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                <?php endif; ?>

                <details class="job-360-hero__more relative">
                    <summary><?php echo e(__('More')); ?></summary>
                    <div class="job-360-hero__more-menu absolute right-0 z-40">
                        <a href="<?php echo e(route('admin.production.floor')); ?>" class="job-360-hero__more-link" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>><?php echo e(__('Back to floor')); ?></a>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $jobCard)): ?>
                            <a href="<?php echo e(route('admin.production.job-cards.edit', $jobCard)); ?>" class="job-360-hero__more-link" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>><?php echo e(__('Edit job')); ?></a>
                        <?php endif; ?>
                        <?php if (! ($hasDispatch)): ?>
                            <?php $__currentLoopData = $secondaryActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('admin.production.job-cards.workspace.partials.primary-action-button', [
                                    'action' => $action,
                                    'completion' => $completion,
                                    'size' => 'sm',
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transition', $jobCard)): ?>
                                <?php if($jobCard->status->canTransitionTo(ProductionJobCardStatus::OnHold)
                                    && $jobCard->status !== ProductionJobCardStatus::InProduction): ?>
                                    <form method="POST" action="<?php echo e(route('admin.production.job-cards.hold', $jobCard)); ?>" <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="job-360-hero__more-link w-full"><?php echo e(__('Hold job')); ?></button>
                                    </form>
                                <?php endif; ?>
                                <?php if($jobCard->status->canTransitionTo(ProductionJobCardStatus::Cancelled)): ?>
                                    <form method="POST" action="<?php echo e(route('admin.production.job-cards.cancel', $jobCard)); ?>" <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="job-360-hero__more-link job-360-hero__more-link--danger w-full"><?php echo e(__('Cancel job')); ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $jobCard)): ?>
                            <form method="POST" action="<?php echo e(route('admin.production.job-cards.destroy', $jobCard)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Permanently delete this job card? This cannot be undone.'))->toHtml() ?>)" <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="job-360-hero__more-link job-360-hero__more-link--danger w-full"><?php echo e(__('Delete job')); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </details>
            </div>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/header.blade.php ENDPATH**/ ?>