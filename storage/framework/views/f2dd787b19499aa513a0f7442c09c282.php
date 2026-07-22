<?php
    $operatorMode = (bool) ($operatorMode ?? false);
?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold text-erp-primary"><?php echo e(__('My Work Queue')); ?></h2>
        <p class="text-xs text-slate-500"><?php echo e(__('Assigned to you — select a job to work inline.')); ?></p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Request #')); ?></th>
                    <th><?php echo e(__('Customer')); ?></th>
                    <th><?php echo e(__('Title')); ?></th>
                    <th><?php echo e(__('Priority')); ?></th>
                    <th><?php echo e(__('Status')); ?></th>
                    <th><?php echo e(__('Due Date')); ?></th>
                    <th><?php echo e(__('Version')); ?></th>
                    <th><?php echo e(__('Actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'cursor-pointer transition-colors hover:bg-violet-50/60',
                            'bg-violet-50 ring-1 ring-inset ring-violet-200' => false,
                            'bg-amber-50/70' => $row['is_late'],
                            'bg-blue-50/50' => $row['is_due_today'] && ! $row['is_late'],
                        ]); ?>"
                        :class="{ 'bg-violet-50 ring-1 ring-inset ring-violet-200': selectedKey === <?php echo \Illuminate\Support\Js::from($row['key'])->toHtml() ?> }"
                        @click="selectRequest(<?php echo \Illuminate\Support\Js::from($row['key'])->toHtml() ?>)"
                        data-urgency-due-today="<?php echo e($row['is_due_today'] ? '1' : '0'); ?>"
                        data-urgency-overdue="<?php echo e($row['is_late'] ? '1' : '0'); ?>"
                        data-urgency-waiting="<?php echo e($row['is_waiting'] ? '1' : '0'); ?>"
                        data-urgency-new="<?php echo e($row['status'] === 'requested' ? '1' : '0'); ?>"
                        x-show="rowVisible($el)"
                    >
                        <td class="font-mono text-xs font-semibold text-erp-accent"><?php echo e($row['request_number']); ?></td>
                        <td><?php echo e($row['customer'] ?? '—'); ?></td>
                        <td class="font-medium"><?php echo e($row['title']); ?></td>
                        <td>
                            <?php
                                $priorityColors = [
                                    'low' => 'bg-slate-100 text-slate-700',
                                    'normal' => 'bg-blue-100 text-blue-700',
                                    'high' => 'bg-amber-100 text-amber-700',
                                    'urgent' => 'bg-rose-100 text-rose-700',
                                ];
                            ?>
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                $priorityColors[$row['priority']] ?? 'bg-slate-100 text-slate-700',
                            ]); ?>"><?php echo e($row['priority_label']); ?></span>
                        </td>
                        <td>
                            <?php
                                $statusColors = [
                                    'requested' => 'bg-slate-100 text-slate-700',
                                    'in_design' => 'bg-blue-100 text-blue-700',
                                    'submitted' => 'bg-indigo-100 text-indigo-700',
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'revision_requested' => 'bg-amber-100 text-amber-700',
                                    'rejected' => 'bg-rose-100 text-rose-700',
                                ];
                            ?>
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                $statusColors[$row['status']] ?? 'bg-slate-100 text-slate-700',
                            ]); ?>"><?php echo e($row['status_label']); ?></span>
                        </td>
                        <td class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-xs', 'font-semibold text-amber-800' => $row['is_late']]); ?>"><?php echo e($row['due_date'] ?? '—'); ?></td>
                        <td class="text-center"><?php echo e($row['version']); ?></td>
                        <td @click.stop>
                            <div class="flex flex-wrap items-center gap-1">
                                <button
                                    type="button"
                                    class="erp-btn-primary px-2 py-1 text-xs"
                                    @click="selectRequest(<?php echo \Illuminate\Support\Js::from($row['key'])->toHtml() ?>)"
                                >
                                    <?php echo e($row['is_editable'] ? __('Work') : __('Open')); ?>

                                </button>
                                <?php if($row['is_editable']): ?>
                                    <button
                                        type="button"
                                        class="erp-btn-secondary px-2 py-1 text-xs"
                                        @click="selectRequest(<?php echo \Illuminate\Support\Js::from($row['key'])->toHtml() ?>, 'designer-desk-files')"
                                    >
                                        <?php echo e(__('Upload')); ?>

                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="py-6 text-center text-sm text-slate-500"><?php echo e(__('Queue empty — see activity below.')); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/desk/partials/table.blade.php ENDPATH**/ ?>