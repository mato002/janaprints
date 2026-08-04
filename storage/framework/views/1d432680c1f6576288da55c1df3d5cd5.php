<?php
    use App\Enums\ProductionJobCardStatus;
    use App\Support\Navigation\WorkspaceEmbed;

    $state = $executionState ?? [];
    $machines = $assignableMachines ?? collect();
    $operators = $state['operators'] ?? collect();
    $phase = $state['phase'] ?? 'other';
    $dispatchSummary = $state['dispatch_summary'] ?? null;
    $hasDispatch = ! empty($dispatchSummary['has_delivery_note']);
    $summary = $dispatchSummary['summary'] ?? [];
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $formTurboAttrs = WorkspaceEmbed::mainFormAttributes();

    $showSchedule = ! $hasDispatch && auth()->user()?->can('schedule', $jobCard)
        && in_array($jobCard->status, [
            ProductionJobCardStatus::Draft,
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::OnHold,
        ], true)
        && ! (($state['needs_operator'] ?? false) || ($state['needs_machine'] ?? false));
?>

<section aria-label="<?php echo e(__('Production')); ?>">

    <?php if($hasDispatch): ?>
        <dl class="job-360-zone__compact-grid">
            <div><dt><?php echo e(__('Delivery note')); ?></dt><dd><a href="<?php echo e($summary['show_url'] ?? '#'); ?>" class="font-mono text-indigo-700 hover:underline" <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>><?php echo e($summary['delivery_note_number'] ?? '—'); ?></a></dd></div>
            <div><dt><?php echo e(__('Courier')); ?></dt><dd><?php echo e($summary['courier'] ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Tracking')); ?></dt><dd class="font-mono"><?php echo e($summary['tracking_number'] ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Recipient')); ?></dt><dd><?php echo e($summary['recipient_name'] ?? '—'); ?></dd></div>
        </dl>
    <?php else: ?>
        <?php if(($state['needs_operator'] ?? false) && (auth()->user()?->can('schedule', $jobCard) || auth()->user()?->can('update', $jobCard)) && ($state['queue_id'] ?? null)): ?>
            <form
                id="assign-operator"
                method="POST"
                action="<?php echo e(route('admin.production.job-cards.assign-operator', $jobCard)); ?>"
                class="job-360-zone__assign-row"
                <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            >
                <?php echo csrf_field(); ?>
                <input type="hidden" name="production_queue_id" value="<?php echo e($state['queue_id']); ?>">
                <label class="job-360-zone__assign-label"><?php echo e(__('Operator')); ?></label>
                <div class="job-360-zone__assign-controls">
                    <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'assigned_operator_id','options' => $operators->map(fn ($operator) => ['value' => $operator->id, 'label' => $operator->name])->values()->all(),'required' => true,'createRoute' => 'admin.operators.quick-create','refreshRoute' => 'admin.lookups.operators','permission' => 'employees.manage','modalTitle' => __('Create operator'),'selectClass' => 'erp-select w-full text-sm','placeholder' => __('Select operator')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'assigned_operator_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($operators->map(fn ($operator) => ['value' => $operator->id, 'label' => $operator->name])->values()->all()),'required' => true,'create-route' => 'admin.operators.quick-create','refresh-route' => 'admin.lookups.operators','permission' => 'employees.manage','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create operator')),'select-class' => 'erp-select w-full text-sm','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select operator'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
                    <button type="submit" class="erp-btn-primary text-sm shrink-0"><?php echo e(__('Assign')); ?></button>
                </div>
            </form>
        <?php endif; ?>

        <?php if(($state['needs_machine'] ?? false) && auth()->user()?->can('machines.assign')): ?>
            <form
                id="assign-machine"
                method="POST"
                action="<?php echo e(route('admin.production.job-cards.assign-machine', $jobCard)); ?>"
                class="job-360-zone__assign-row"
                <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            >
                <?php echo csrf_field(); ?>
                <label class="job-360-zone__assign-label"><?php echo e(__('Machine')); ?></label>
                <div class="job-360-zone__assign-controls">
                    <select name="assigned_machine_asset_id" class="erp-select w-full text-sm" required>
                        <option value=""><?php echo e(__('Select machine')); ?></option>
                        <?php $__currentLoopData = $machines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($machine->fixed_asset_id); ?>"><?php echo e($machine->asset?->asset_name); ?> (<?php echo e($machine->machine_code); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="erp-btn-primary text-sm shrink-0"><?php echo e(__('Assign')); ?></button>
                </div>
            </form>
        <?php endif; ?>

        <dl class="job-360-zone__compact-grid">
            <div>
                <dt><?php echo e(__('Queue')); ?></dt>
                <dd>
                    <?php if($state['queue_position'] ?? null): ?>
                        #<?php echo e($state['queue_position']); ?>

                        <?php if($state['queue_status'] ?? null): ?>
                            · <?php echo e(\App\Enums\ProductionQueueStatus::tryFrom($state['queue_status'])?->label() ?? $state['queue_status']); ?>

                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo e(__('Not queued')); ?>

                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt><?php echo e(__('Stage')); ?></dt>
                <dd><?php echo e($state['stage_name'] ?? $state['work_center'] ?? '—'); ?></dd>
            </div>
        </dl>

        <?php if($showSchedule): ?>
            <form method="POST" action="<?php echo e(route('admin.production.job-cards.schedule', $jobCard)); ?>" class="job-360-zone__inline-form" <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                <?php echo csrf_field(); ?>
                <div class="job-360-zone__form-field">
                    <label><?php echo e(__('Planned start')); ?></label>
                    <input type="date" name="planned_start_date" class="erp-input text-sm" value="<?php echo e($jobCard->planned_start_date?->format('Y-m-d')); ?>" required>
                </div>
                <div class="job-360-zone__form-field">
                    <label><?php echo e(__('Planned end')); ?></label>
                    <input type="date" name="planned_end_date" class="erp-input text-sm" value="<?php echo e($jobCard->planned_end_date?->format('Y-m-d')); ?>" required>
                </div>
                <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Update schedule')); ?></button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\operations-zone.blade.php ENDPATH**/ ?>