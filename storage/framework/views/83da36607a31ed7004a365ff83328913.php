<?php
    $checks = $tabData['checks'] ?? null;
    $snapshot = $tabData['snapshot'] ?? null;
    $rework = $tabData['rework_summary'] ?? [];
    $serials = $tabData['serial_ranges'] ?? [];
    $checklistItems = $snapshot?->checklist_items ?? [];
?>

<?php if($tabData['qc_blocking'] ?? false): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4 border-red-200 bg-red-50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 border-red-200 bg-red-50']); ?>
        <p class="text-sm font-medium text-red-900"><?php echo e(__('QC failed or awaiting approval — dispatch blocked')); ?></p>
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

<?php if($pending = ($tabData['pending_customer_approval'] ?? null)): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4 border-amber-200 bg-amber-50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 border-amber-200 bg-amber-50']); ?>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm text-amber-900"><?php echo e(__('Awaiting customer approval for conditional pass inspection.')); ?></p>
            <?php if($tabData['can_approve_customer'] ?? false): ?>
                <form method="POST" action="<?php echo e(route('admin.production.quality-checks.approve-customer', [$jobCard, $pending])); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Record customer approval')); ?></button>
                </form>
            <?php endif; ?>
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
<?php endif; ?>

<?php if(! empty($serials['allocated_start'])): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Serial ranges')); ?></h3>
        <dl class="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
            <div><dt class="text-slate-500"><?php echo e(__('Allocated')); ?></dt><dd class="font-mono tabular-nums"><?php echo e($serials['allocated_start']); ?> – <?php echo e($serials['allocated_end']); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Produced to')); ?></dt><dd class="font-mono tabular-nums"><?php echo e($serials['produced_end'] ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Spoiled qty')); ?></dt><dd class="tabular-nums"><?php echo e($serials['spoiled_quantity'] ?? 0); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Production loss')); ?></dt><dd class="tabular-nums"><?php echo e($serials['loss_metrics']['production_loss_quantity'] ?? 0); ?></dd></div>
        </dl>
        <?php if(($serials['spoiled_ranges'] ?? collect())->isNotEmpty()): ?>
            <table class="erp-table mt-3 w-full text-sm">
                <thead><tr><th><?php echo e(__('Spoiled range')); ?></th><th><?php echo e(__('Qty')); ?></th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $serials['spoiled_ranges']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="font-mono"><?php echo e($range->serial_start); ?> – <?php echo e($range->serial_end); ?></td>
                            <td class="tabular-nums"><?php echo e($range->quantity); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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
<?php endif; ?>

<?php if($tabData['can_record'] ?? false): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6','id' => 'add-qc','xData' => '{ decision: \'passed\' }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','id' => 'add-qc','x-data' => '{ decision: \'passed\' }']); ?>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Record inspection')); ?></h3>
        <form method="POST" action="<?php echo e(route('admin.production.quality-checks.store', $jobCard)); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                    <label class="erp-label text-xs"><?php echo e(__('Inspection date')); ?></label>
                    <input type="date" name="inspection_date" class="erp-input w-full text-sm" value="<?php echo e(now()->toDateString()); ?>">
                </div>
                <div>
                    <label class="erp-label text-xs"><?php echo e(__('Decision')); ?></label>
                    <select name="result" class="erp-input w-full text-sm" required x-model="decision">
                        <option value="passed"><?php echo e(__('Pass')); ?></option>
                        <option value="failed"><?php echo e(__('Fail')); ?></option>
                        <option value="conditional_pass"><?php echo e(__('Conditional pass')); ?></option>
                    </select>
                </div>
                <div x-show="decision === 'conditional_pass'" x-cloak>
                    <label class="inline-flex items-center gap-2 text-sm mt-6">
                        <input type="checkbox" name="requires_customer_approval" value="1">
                        <?php echo e(__('Requires customer approval')); ?>

                    </label>
                </div>
            </div>

            <?php if(count($checklistItems) > 0): ?>
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase text-slate-600"><?php echo e(__('Checklist')); ?></h4>
                    <table class="erp-table w-full text-sm">
                        <thead><tr><th><?php echo e(__('Item')); ?></th><th><?php echo e(__('Pass')); ?></th><th><?php echo e(__('Fail')); ?></th></tr></thead>
                        <tbody>
                            <?php $__currentLoopData = $checklistItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php echo e($item['label']); ?>

                                        <input type="hidden" name="checklist[<?php echo e($index); ?>][line_id]" value="<?php echo e($item['line_id'] ?? ''); ?>">
                                        <input type="hidden" name="checklist[<?php echo e($index); ?>][label]" value="<?php echo e($item['label']); ?>">
                                    </td>
                                    <td><input type="radio" name="checklist[<?php echo e($index); ?>][passed]" value="1"></td>
                                    <td><input type="radio" name="checklist[<?php echo e($index); ?>][passed]" value="0"></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2" x-show="decision === 'failed' || decision === 'conditional_pass'" x-cloak>
                <div>
                    <label class="erp-label text-xs"><?php echo e(__('Fail reason')); ?></label>
                    <select name="fail_reason" class="erp-input w-full text-sm">
                        <option value=""><?php echo e(__('—')); ?></option>
                        <?php $__currentLoopData = $tabData['fail_reasons'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($reason->value); ?>"><?php echo e($reason->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label text-xs"><?php echo e(__('Rework reason')); ?></label>
                    <select name="rework_reason" class="erp-input w-full text-sm">
                        <option value=""><?php echo e(__('—')); ?></option>
                        <?php $__currentLoopData = $tabData['rework_reasons'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($reason->value); ?>"><?php echo e($reason->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label text-xs"><?php echo e(__('Est. rework qty')); ?></label>
                    <input type="number" step="0.001" min="0" name="estimated_rework_qty" class="erp-input w-full text-sm">
                </div>
                <div>
                    <label class="erp-label text-xs"><?php echo e(__('Actual rework qty')); ?></label>
                    <input type="number" step="0.001" min="0" name="actual_rework_qty" class="erp-input w-full text-sm">
                </div>
            </div>

            <div>
                <label class="erp-label text-xs"><?php echo e(__('Notes')); ?></label>
                <textarea name="comments" class="erp-input w-full text-sm" rows="2"></textarea>
            </div>
            <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Save inspection')); ?></button>
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

<?php if(($rework['count'] ?? 0) > 0): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Rework summary')); ?></h3>
        <p class="mb-2 text-sm text-slate-600"><?php echo e(__('Est. total')); ?>: <?php echo e($rework['estimated_total']); ?> · <?php echo e(__('Actual total')); ?>: <?php echo e($rework['actual_total']); ?></p>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Date')); ?></th>
                        <th><?php echo e(__('Reason')); ?></th>
                        <th><?php echo e(__('Est. qty')); ?></th>
                        <th><?php echo e(__('Actual qty')); ?></th>
                        <th><?php echo e(__('Notes')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $rework['lines'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="tabular-nums"><?php echo e($line['inspection_date']); ?></td>
                            <td><?php echo e($line['rework_reason']); ?></td>
                            <td class="tabular-nums"><?php echo e($line['estimated_rework_qty']); ?></td>
                            <td class="tabular-nums"><?php echo e($line['actual_rework_qty']); ?></td>
                            <td><?php echo e($line['notes'] ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php endif; ?>

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
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Inspection history')); ?></h3>
    <?php if($checks && $checks->count() > 0): ?>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Decision')); ?></th>
                        <th><?php echo e(__('Inspector')); ?></th>
                        <th><?php echo e(__('Date')); ?></th>
                        <th><?php echo e(__('Fail / rework')); ?></th>
                        <th><?php echo e(__('Rework qty')); ?></th>
                        <th><?php echo e(__('Customer approval')); ?></th>
                        <th><?php echo e(__('Notes')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($failed = $check->result->isBlocking()); ?>
                        <tr class="<?php echo e($failed && ! $check->customer_approved_at ? 'bg-red-50' : ''); ?>">
                            <td><?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $check->result->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($check->result->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?></td>
                            <td><?php echo e($check->checker?->name ?? '—'); ?></td>
                            <td class="tabular-nums"><?php echo e($check->inspection_date?->format('Y-m-d') ?? $check->checked_at?->format('Y-m-d H:i')); ?></td>
                            <td><?php echo e($check->rework_reason?->label() ?? $check->fail_reason?->label() ?? '—'); ?></td>
                            <td class="tabular-nums"><?php echo e($check->estimated_rework_qty ?? '—'); ?> / <?php echo e($check->actual_rework_qty ?? '—'); ?></td>
                            <td>
                                <?php if($check->customer_approved_at): ?>
                                    <?php echo e($check->customerApprover?->name); ?> · <?php echo e($check->customer_approved_at->format('Y-m-d')); ?>

                                <?php elseif($check->requires_customer_approval): ?>
                                    <?php echo e(__('Pending')); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($check->comments ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php if($checks->hasPages()): ?><div class="mt-4"><?php echo e($checks->links()); ?></div><?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No inspections'),'description' => __('Quality inspections will appear here once recorded.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No inspections')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Quality inspections will appear here once recorded.'))]); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\quality.blade.php ENDPATH**/ ?>