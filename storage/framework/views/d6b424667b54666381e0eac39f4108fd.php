<?php
    $state = $executionState ?? [];
    $phase = $state['phase'] ?? 'other';
    $primary = $primaryAction ?? null;
    $secondary = $secondaryActions ?? [];
    $machines = $assignableMachines ?? collect();
    $operators = $state['operators'] ?? collect();
    $dispatchSummary = $state['dispatch_summary'] ?? null;
    $hasDispatch = ! empty($dispatchSummary['has_delivery_note']);
    $workflowNextStep = $state['workflow_next_step'] ?? null;
    $summary = $dispatchSummary['summary'] ?? [];
    $dispatchActions = $dispatchSummary['actions'] ?? ['primary' => null, 'secondary' => [], 'danger' => []];
    $showAssignment = ! $hasDispatch && in_array($phase, ['awaiting_operator', 'awaiting_machine', 'ready', 'awaiting_accept', 'queued'], true);
    $showSchedule = ! $hasDispatch && auth()->user()?->can('schedule', $jobCard)
        && in_array($jobCard->status, [
            \App\Enums\ProductionJobCardStatus::Draft,
            \App\Enums\ProductionJobCardStatus::Queued,
            \App\Enums\ProductionJobCardStatus::OnHold,
        ], true)
        && ! $showAssignment;
?>

<div class="job-360-execution mb-4 rounded-lg border border-erp-border bg-white">
    <div class="grid gap-4 border-b border-erp-border px-4 py-4 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-erp-primary"></span>
                <h2 class="text-base font-semibold text-slate-900"><?php echo e($state['phase_label'] ?? $jobCard->status->label()); ?></h2>
                <?php if($hasDispatch && ! empty($summary['status'])): ?>
                    <?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $summary['status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['status'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
                <?php elseif(! empty($state['queue_status'])): ?>
                    <span class="erp-badge bg-slate-100 text-slate-700"><?php echo e(\App\Enums\ProductionQueueStatus::tryFrom($state['queue_status'])?->label() ?? $state['queue_status']); ?></span>
                <?php endif; ?>
            </div>
            <p class="mt-2 text-sm text-slate-600"><?php echo e($state['next_action'] ?? ''); ?></p>

            <?php if($hasDispatch): ?>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Delivery note')); ?></dt>
                        <dd class="mt-0.5 font-mono font-medium text-indigo-600">
                            <a href="<?php echo e($summary['show_url'] ?? '#'); ?>" data-turbo-frame="erp-main"><?php echo e($summary['delivery_note_number'] ?? '—'); ?></a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Courier')); ?></dt>
                        <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($summary['courier'] ?? '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Tracking')); ?></dt>
                        <dd class="mt-0.5 font-mono text-sm font-medium text-slate-900"><?php echo e($summary['tracking_number'] ?? '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Recipient')); ?></dt>
                        <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($summary['recipient_name'] ?? '—'); ?></dd>
                    </div>
                </dl>
            <?php else: ?>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Current stage')); ?></dt>
                        <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($state['stage_name'] ?? $state['work_center'] ?? '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Queue position')); ?></dt>
                        <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($state['queue_position'] ? '#'.$state['queue_position'] : '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Assigned machine')); ?></dt>
                        <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($state['machine_name'] ?? __('Not assigned')); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Assigned operator')); ?></dt>
                        <dd class="mt-0.5 font-medium text-slate-900"><?php echo e($state['operator_name'] ?? __('Not assigned')); ?></dd>
                    </div>
                </dl>
            <?php endif; ?>
        </div>

        <div class="flex flex-col justify-between gap-3 lg:col-span-4">
            <div class="flex flex-wrap items-center justify-end gap-2">
                <?php if($hasDispatch): ?>
                    <?php if($dispatchActions['primary'] ?? null): ?>
                        <a href="<?php echo e($dispatchActions['primary']['url']); ?>" class="erp-btn-primary text-sm" data-turbo-frame="erp-main"><?php echo e($dispatchActions['primary']['label']); ?></a>
                    <?php endif; ?>
                    <?php $__currentLoopData = $dispatchActions['secondary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            href="<?php echo e($action['url']); ?>"
                            class="erp-btn-secondary text-sm"
                            <?php if(($action['target'] ?? null) === '_blank'): ?> target="_blank" rel="noopener" <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>
                        ><?php echo e($action['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $dispatchActions['danger'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($action['url']); ?>" class="text-sm font-medium text-red-600 hover:underline" data-turbo-frame="erp-main"><?php echo e($action['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <?php if($workflowNextStep): ?>
                        <a href="<?php echo e($workflowNextStep['url']); ?>" class="erp-btn-primary text-sm" data-turbo-frame="erp-main"><?php echo e($workflowNextStep['label']); ?></a>
                    <?php endif; ?>
                    <?php if($primary): ?>
                        <?php if(($primary['type'] ?? '') === 'post'): ?>
                            <form method="POST" action="<?php echo e($primary['url']); ?>" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="<?php echo e(($primary['variant'] ?? '') === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary'); ?> text-sm">
                                    <?php echo e($primary['label']); ?>

                                </button>
                            </form>
                        <?php elseif(($primary['type'] ?? '') === 'link' && ! str_contains((string) ($primary['url'] ?? ''), '#assign-')): ?>
                            <a href="<?php echo e($primary['url']); ?>" class="<?php echo e(($primary['variant'] ?? '') === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary'); ?> text-sm" data-turbo-frame="erp-main"><?php echo e($primary['label']); ?></a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php $__currentLoopData = $secondary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(($action['type'] ?? '') === 'post'): ?>
                            <form method="POST" action="<?php echo e($action['url']); ?>" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="erp-btn-secondary text-sm"><?php echo e($action['label']); ?></button>
                            </form>
                        <?php else: ?>
                            <a href="<?php echo e($action['url']); ?>" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main"><?php echo e($action['label']); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>

            <details class="text-right text-sm">
                <summary class="cursor-pointer list-none text-slate-500 hover:text-erp-primary [&::-webkit-details-marker]:hidden">
                    <?php echo e(__('More actions')); ?> ▾
                </summary>
                <div class="mt-2 flex flex-wrap justify-end gap-3">
                    <?php if (! ($hasDispatch)): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transition', $jobCard)): ?>
                            <?php if($jobCard->status->canTransitionTo(\App\Enums\ProductionJobCardStatus::OnHold)
                                && $jobCard->status !== \App\Enums\ProductionJobCardStatus::InProduction
                                && in_array($phase, ['awaiting_operator', 'awaiting_machine', 'ready', 'queued'], true)): ?>
                                <form method="POST" action="<?php echo e(route('admin.production.job-cards.hold', $jobCard)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-sm text-slate-600 hover:underline"><?php echo e(__('Hold')); ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if($jobCard->status->canTransitionTo(\App\Enums\ProductionJobCardStatus::Cancelled)): ?>
                                <form method="POST" action="<?php echo e(route('admin.production.job-cards.cancel', $jobCard)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-sm text-red-600 hover:underline"><?php echo e(__('Cancel job')); ?></button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $jobCard)): ?>
                        <form method="POST" action="<?php echo e(route('admin.production.job-cards.destroy', $jobCard)); ?>" class="inline" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Permanently delete this job card? This cannot be undone.'))->toHtml() ?>)">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-sm text-red-700 hover:underline"><?php echo e(__('Delete job')); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </details>
        </div>
    </div>

    <?php if(($state['needs_operator'] ?? false) && (auth()->user()?->can('schedule', $jobCard) || auth()->user()?->can('update', $jobCard)) && ($state['queue_id'] ?? null)): ?>
        <form
            id="assign-operator"
            method="POST"
            action="<?php echo e(route('admin.production.job-cards.assign-operator', $jobCard)); ?>"
            class="flex flex-wrap items-end gap-3 border-b border-erp-border bg-slate-50 px-4 py-3"
        >
            <?php echo csrf_field(); ?>
            <input type="hidden" name="production_queue_id" value="<?php echo e($state['queue_id']); ?>">
            <div class="min-w-[16rem] flex-1">
                <label class="block text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Assign operator')); ?></label>
                <select name="assigned_operator_id" class="erp-select w-full text-sm" required>
                    <option value=""><?php echo e(__('Select operator')); ?></option>
                    <?php $__currentLoopData = $operators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($operator->id); ?>"><?php echo e($operator->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Assign operator')); ?></button>
        </form>
    <?php endif; ?>

    <?php if(($state['needs_machine'] ?? false) && auth()->user()?->can('machines.assign')): ?>
        <form
            id="assign-machine"
            method="POST"
            action="<?php echo e(route('admin.production.job-cards.assign-machine', $jobCard)); ?>"
            class="flex flex-wrap items-end gap-3 border-b border-erp-border bg-slate-50 px-4 py-3"
        >
            <?php echo csrf_field(); ?>
            <div class="min-w-[16rem] flex-1">
                <label class="block text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Assign machine')); ?></label>
                    <select name="assigned_machine_asset_id" class="erp-select w-full text-sm" required>
                    <option value=""><?php echo e(__('Select machine')); ?></option>
                    <?php $__currentLoopData = $machines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($machine->fixed_asset_id); ?>"><?php echo e($machine->asset?->asset_name); ?> (<?php echo e($machine->machine_code); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Assign machine')); ?></button>
        </form>
    <?php endif; ?>

    <?php if($showSchedule): ?>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.schedule', $jobCard)); ?>" class="flex flex-wrap items-end gap-2 px-4 py-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Planned start')); ?></label>
                <input type="date" name="planned_start_date" class="erp-input text-sm py-1" value="<?php echo e($jobCard->planned_start_date?->format('Y-m-d')); ?>" required>
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Planned end')); ?></label>
                <input type="date" name="planned_end_date" class="erp-input text-sm py-1" value="<?php echo e($jobCard->planned_end_date?->format('Y-m-d')); ?>" required>
            </div>
            <button type="submit" class="erp-btn-secondary text-sm py-1"><?php echo e(__('Update schedule')); ?></button>
        </form>
    <?php endif; ?>
</div>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production.outputs.post')): ?>
    <?php
        $activeTab = $activeTab ?? null;
        $onOutputsTab = $activeTab === 'outputs';
        $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    ?>
    <?php if (! ($onOutputsTab)): ?>
        <?php echo $__env->make('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
            'jobCard' => $jobCard,
            'completion' => $completion,
            'finishedItems' => $finishedItems ?? collect(),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/execution-state-banner.blade.php ENDPATH**/ ?>