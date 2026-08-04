<?php
    $operations = $tabData['operations'] ?? null;
    $queues = $tabData['queues'] ?? collect();
    $controls = $tabData['controls'] ?? null;
?>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Production queue')); ?></h3>
        <?php $__empty_1 = true; $__currentLoopData = $queues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border-b border-erp-border py-2 text-sm last:border-0">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <span class="font-medium"><?php echo e($entry->workCenter?->name); ?></span>
                        <span class="text-slate-500"> — #<?php echo e($entry->queue_position); ?> (<?php echo e(str_replace('_', ' ', $entry->status->value)); ?>)</span>
                    </div>
                    <?php if($tabData['can_manage_queue'] ?? false): ?>
                        <div class="flex flex-wrap items-center gap-2">
                            <form method="POST" action="<?php echo e(route('admin.production.queues.update', [$jobCard, $entry])); ?>" class="inline-flex flex-wrap items-center gap-1">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="number" name="queue_position" class="erp-input w-16 text-xs py-1" value="<?php echo e($entry->queue_position); ?>" min="1" required>
                                <select name="status" class="erp-input text-xs py-1">
                                    <?php $__currentLoopData = $tabData['queue_statuses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($status->value); ?>" <?php if($entry->status === $status): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Update')); ?></button>
                            </form>
                            <form method="POST" action="<?php echo e(route('admin.production.queues.destroy', [$jobCard, $entry])); ?>" class="inline" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Remove this queue entry?'))->toHtml() ?>)">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-xs text-red-600 hover:underline"><?php echo e(__('Remove')); ?></button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500"><?php echo e(__('No queue entries.')); ?></p>
        <?php endif; ?>

        <?php if($tabData['can_queue'] ?? false): ?>
            <form method="POST" action="<?php echo e(route('admin.production.queues.store', $jobCard)); ?>" class="mt-4 space-y-2" id="queue-form">
                <?php echo csrf_field(); ?>
                <select name="work_center_id" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = $tabData['work_centers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($wc->id); ?>"><?php echo e($wc->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="number" name="queue_position" class="erp-input w-full text-sm" value="1" min="1" required>
                <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Add to queue')); ?></button>
            </form>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

    <?php if($tabData['can_log'] ?? false): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['id' => 'log-operation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'log-operation']); ?>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Log operation')); ?></h3>
            <form method="POST" action="<?php echo e(route('admin.production.operations.store', $jobCard)); ?>" class="grid grid-cols-1 gap-2">
                <?php echo csrf_field(); ?>
                <select name="work_center_id" class="erp-input text-sm" required>
                    <?php $__currentLoopData = $tabData['work_centers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($wc->id); ?>"><?php echo e($wc->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="production_stage_id" class="erp-input text-sm" required>
                    <?php $__currentLoopData = $tabData['stages'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($stage->id); ?>"><?php echo e($stage->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php if($tabData['operator_assignment_available'] ?? false): ?>
                    <select name="assigned_employee_id" class="erp-input text-sm">
                        <option value=""><?php echo e(__('Assign operator (optional)')); ?></option>
                        <?php $__currentLoopData = $tabData['operators'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($employee->id); ?>">
                                <?php echo e(trim($employee->first_name.' '.$employee->last_name)); ?>

                                <?php if($employee->employee_number): ?> (<?php echo e($employee->employee_number); ?>) <?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php endif; ?>
                <textarea name="remarks" class="erp-input text-sm" rows="2" placeholder="<?php echo e(__('Notes (optional)')); ?>"></textarea>
                <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Log operation')); ?></button>
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    <?php endif; ?>
</div>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Operations log')); ?></h3>
    <?php if($operations && $operations->count() > 0): ?>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Stage')); ?></th>
                        <th><?php echo e(__('Work center')); ?></th>
                        <th><?php echo e(__('Operator')); ?></th>
                        <th><?php echo e(__('Started')); ?></th>
                        <th><?php echo e(__('Completed')); ?></th>
                        <th><?php echo e(__('Duration')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th><?php echo e(__('Notes')); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $operations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $execStatus = $controls ? $controls->operationExecutionStatus($op, $jobCard) : 'pending';
                            $duration = ($op->started_at && $op->ended_at)
                                ? $op->started_at->diffForHumans($op->ended_at, true)
                                : ($op->started_at ? __('In progress') : '—');
                            $operator = $op->assignedEmployee?->full_name
                                ?? trim(($op->assignedEmployee?->first_name ?? '').' '.($op->assignedEmployee?->last_name ?? ''));
                        ?>
                        <tr>
                            <td><?php echo e($op->stage?->name ?? '—'); ?></td>
                            <td><?php echo e($op->workCenter?->name ?? '—'); ?></td>
                            <td><?php echo e($operator !== '' ? $operator : '—'); ?></td>
                            <td class="tabular-nums"><?php echo e($op->started_at?->format('Y-m-d H:i') ?? '—'); ?></td>
                            <td class="tabular-nums"><?php echo e($op->ended_at?->format('Y-m-d H:i') ?? '—'); ?></td>
                            <td><?php echo e($duration); ?></td>
                            <td>
                                <?php
                                    $badgeClass = match ($execStatus) {
                                        'completed' => 'bg-emerald-100 text-emerald-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'blocked' => 'bg-red-100 text-red-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                ?>
                                <span class="erp-badge <?php echo e($badgeClass); ?>"><?php echo e(str_replace('_', ' ', $execStatus)); ?></span>
                            </td>
                            <td class="max-w-[12rem] truncate" title="<?php echo e($op->remarks); ?>"><?php echo e($op->remarks ?? '—'); ?></td>
                            <td class="text-end whitespace-nowrap">
                                <?php if(($tabData['can_assign'] ?? false) && ! $op->ended_at): ?>
                                    <form method="POST" action="<?php echo e(route('admin.production.operations.update', [$jobCard, $op])); ?>" class="inline-flex items-center gap-1">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <select name="assigned_employee_id" class="erp-input text-xs py-1 max-w-[8rem]">
                                            <option value=""><?php echo e(__('Unassigned')); ?></option>
                                            <?php $__currentLoopData = $tabData['operators'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($employee->id); ?>" <?php if($op->assigned_employee_id === $employee->id): echo 'selected'; endif; ?>>
                                                    <?php echo e($employee->first_name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Assign')); ?></button>
                                    </form>
                                <?php endif; ?>
                                <?php if(($tabData['can_complete_op'] ?? false) && $op->started_at && ! $op->ended_at): ?>
                                    <form method="POST" action="<?php echo e(route('admin.production.operations.complete', [$jobCard, $op])); ?>" class="inline mt-1">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Complete')); ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php if($operations->hasPages()): ?>
            <div class="mt-4"><?php echo e($operations->links()); ?></div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No operations logged'),'description' => __('Start production and log operations to track progress.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No operations logged')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Start production and log operations to track progress.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\operations.blade.php ENDPATH**/ ?>