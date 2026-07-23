<?php
    use App\Enums\ProductionJobCardStatus;

    $executionState = $executionState ?? [];
    $primaryAction = $primaryAction ?? null;
    $secondaryActions = $secondaryActions ?? [];
    $workflowPresentation = $workflowPresentation ?? null;
    $dispatchSummary = $dispatchSummary ?? null;

    $stageLabel = $workflowPresentation['phase_label']
        ?? $executionState['phase_label']
        ?? $header['status']?->label()
        ?? '—';

    $stagePhase = $workflowPresentation['phase'] ?? $executionState['phase'] ?? 'other';
    $stageTone = match (true) {
        in_array($stagePhase, ['dispatch'], true) => 'success',
        in_array($stagePhase, ['awaiting_fg_post', 'dispatch_blocked'], true) => 'warning',
        in_array($stagePhase, ['cancelled'], true) => 'danger',
        in_array($stagePhase, ['in_progress', 'qc'], true) => 'info',
        default => 'neutral',
    };

    $productName = $header['product_name'] ?? __('No product');
    $customerName = $header['customer_name'] ?? __('No customer');
    $qtyLabel = isset($header['quantity']) && (float) $header['quantity'] > 0
        ? number_format((float) $header['quantity'], 0)
        : null;
    $dueLabel = $header['due_date']
        ? ($header['due_date']->isToday() ? __('Due today') : $header['due_date']->format('M j, Y'))
        : null;
    $progress = (int) ($header['progress_percent'] ?? 0);

    $dispatchSummaryState = $executionState['dispatch_summary'] ?? $dispatchSummary;
    $hasDispatch = ! empty($dispatchSummaryState['has_delivery_note']);
    $dispatchActions = $dispatchSummaryState['actions'] ?? ['primary' => null, 'secondary' => [], 'danger' => []];
    $workflowNextStep = $executionState['workflow_next_step'] ?? null;

    $heroAction = $hasDispatch
        ? ($dispatchActions['primary'] ?? null)
        : ($workflowNextStep
            ? ['label' => $workflowNextStep['label'], 'type' => 'link', 'url' => $workflowNextStep['url'], 'variant' => 'primary']
            : $primaryAction);
?>

<header class="job-360-hero mb-4">
    <div class="job-360-hero__shell">
        <div class="job-360-hero__main">
            <div class="job-360-hero__identity">
                <p class="job-360-hero__eyebrow">
                    <span class="font-mono"><?php echo e($header['job_number']); ?></span>
                    <?php if($header['is_delayed']): ?>
                        <span class="job-360-pill job-360-pill--danger"><?php echo e(__('Delayed')); ?></span>
                    <?php endif; ?>
                    <span class="job-360-pill job-360-pill--neutral"><?php echo e(str_replace('_', ' ', $header['priority']->value)); ?></span>
                </p>

                <h1 class="job-360-hero__title"><?php echo e($productName); ?></h1>
                <p class="job-360-hero__subtitle"><?php echo e($customerName); ?></p>

                <?php if($jobCard->salesOrder): ?>
                    <p class="mt-1.5 text-sm text-slate-600">
                        <span class="text-slate-500"><?php echo e(__('Linked sales order')); ?>:</span>
                        <a
                            href="<?php echo e(route('admin.sales-orders.show', $jobCard->salesOrder)); ?>"
                            class="font-mono font-medium text-erp-accent underline decoration-erp-accent/40 underline-offset-2 hover:decoration-erp-accent"
                            data-turbo-frame="erp-main"
                            data-turbo-action="advance"
                        ><?php echo e($jobCard->salesOrder->order_number); ?></a>
                    </p>
                <?php endif; ?>

                <div class="job-360-hero__meta">
                    <?php if($qtyLabel): ?>
                        <span class="job-360-hero__meta-item">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'cube','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cube','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                            <?php echo e(__('Qty :qty', ['qty' => $qtyLabel])); ?>

                        </span>
                    <?php endif; ?>
                    <?php if($dueLabel): ?>
                        <span class="job-360-hero__meta-item">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'calendar','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                            <?php echo e($dueLabel); ?>

                        </span>
                    <?php endif; ?>
                    <?php if($header['machine_name'] ?? $header['work_center'] ?? null): ?>
                        <span class="job-360-hero__meta-item hidden sm:inline-flex">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'cog','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cog','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                            <?php echo e($header['machine_name'] ?? $header['work_center']); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="job-360-hero__status">
                <p class="job-360-hero__status-label"><?php echo e(__("Today's action")); ?></p>
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['job-360-hero__stage', 'job-360-hero__stage--'.$stageTone]); ?>">
                    <span class="job-360-hero__stage-dot" aria-hidden="true"></span>
                    <span class="job-360-hero__stage-text"><?php echo e($stageLabel); ?></span>
                </div>

                <div class="job-360-hero__progress" aria-label="<?php echo e(__('Workflow progress')); ?>">
                    <div class="job-360-hero__progress-track">
                        <div class="job-360-hero__progress-fill" style="width: <?php echo e($progress); ?>%"></div>
                    </div>
                    <span class="job-360-hero__progress-value tabular-nums"><?php echo e($progress); ?>%</span>
                </div>

                <p class="job-360-hero__next-action"><?php echo e($executionState['next_action'] ?? ''); ?></p>

                <?php if(! empty($executionState['readiness_facts'])): ?>
                    <dl class="job-360-hero__readiness">
                        <?php $__currentLoopData = $executionState['readiness_facts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['job-360-hero__readiness-row', 'job-360-hero__readiness-row--'.$fact['tone']]); ?>">
                                <dt><?php echo e($fact['label']); ?></dt>
                                <dd><?php echo e($fact['value']); ?></dd>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </dl>
                <?php endif; ?>
            </div>
        </div>

        <div class="job-360-hero__actions">
            <div class="job-360-hero__action-stack">
                <?php if($hasDispatch): ?>
                    <?php if($dispatchActions['primary'] ?? null): ?>
                        <a href="<?php echo e($dispatchActions['primary']['url']); ?>" class="job-360-hero__action erp-btn-primary" data-turbo-frame="erp-main">
                            <?php echo e($dispatchActions['primary']['label']); ?>

                        </a>
                    <?php endif; ?>
                    <?php $__currentLoopData = $dispatchActions['secondary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            href="<?php echo e($action['url']); ?>"
                            class="erp-btn-secondary text-sm"
                            <?php if(($action['target'] ?? null) === '_blank'): ?> target="_blank" rel="noopener" <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>
                        ><?php echo e($action['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <?php echo $__env->make('admin.production.job-cards.workspace.partials.primary-action-button', [
                        'action' => $heroAction,
                        'completion' => $completion,
                        'size' => 'lg',
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
            </div>

            <details class="job-360-hero__more">
                <summary><?php echo e(__('More actions')); ?></summary>
                <div class="job-360-hero__more-menu">
                    <a href="<?php echo e(route('admin.production.floor')); ?>" class="job-360-hero__more-link" data-turbo-frame="erp-main"><?php echo e(__('Back to floor')); ?></a>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $jobCard)): ?>
                        <a href="<?php echo e(route('admin.production.job-cards.edit', $jobCard)); ?>" class="job-360-hero__more-link" data-turbo-frame="erp-main"><?php echo e(__('Edit job')); ?></a>
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
                                <form method="POST" action="<?php echo e(route('admin.production.job-cards.hold', $jobCard)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="job-360-hero__more-link"><?php echo e(__('Hold job')); ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if($jobCard->status->canTransitionTo(ProductionJobCardStatus::Cancelled)): ?>
                                <form method="POST" action="<?php echo e(route('admin.production.job-cards.cancel', $jobCard)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="job-360-hero__more-link job-360-hero__more-link--danger"><?php echo e(__('Cancel job')); ?></button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $jobCard)): ?>
                        <form method="POST" action="<?php echo e(route('admin.production.job-cards.destroy', $jobCard)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Permanently delete this job card? This cannot be undone.'))->toHtml() ?>)">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="job-360-hero__more-link job-360-hero__more-link--danger"><?php echo e(__('Delete job')); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </details>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\header.blade.php ENDPATH**/ ?>