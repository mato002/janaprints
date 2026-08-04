<?php
    $sessions = $tabData['sessions'] ?? null;
    $metrics = $tabData['metrics'] ?? [];
?>

<div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><p class="text-xs text-slate-500"><?php echo e(__('Sessions')); ?></p><p class="text-2xl font-bold"><?php echo e($metrics['session_count'] ?? 0); ?></p> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
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
<?php $component->withAttributes([]); ?><p class="text-xs text-slate-500"><?php echo e(__('Total produced')); ?></p><p class="text-2xl font-bold"><?php echo e(number_format($metrics['total_produced'] ?? 0, 0)); ?></p> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
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
<?php $component->withAttributes([]); ?><p class="text-xs text-slate-500"><?php echo e(__('Total waste')); ?></p><p class="text-2xl font-bold text-red-700"><?php echo e(number_format($metrics['total_waste'] ?? 0, 0)); ?></p> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
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
<?php $component->withAttributes([]); ?><p class="text-xs text-slate-500"><?php echo e(__('Waste reasons')); ?></p><p class="text-sm"><?php echo e(count($metrics['waste_by_reason'] ?? [])); ?></p> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
</div>

<?php if($tabData['can_log'] ?? false): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6','id' => 'log-session']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','id' => 'log-session']); ?>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Record Production Session')); ?></h3>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.sessions.store', $jobCard)); ?>" class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            <?php echo csrf_field(); ?>
            <div><label class="erp-label"><?php echo e(__('Start time')); ?></label><input type="datetime-local" name="started_at" class="erp-input w-full" required></div>
            <div><label class="erp-label"><?php echo e(__('End time')); ?></label><input type="datetime-local" name="ended_at" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Expected quantity')); ?></label><input type="number" step="0.001" name="expected_quantity" class="erp-input w-full" required></div>
            <div><label class="erp-label"><?php echo e(__('Produced quantity')); ?></label><input type="number" step="0.001" name="produced_quantity" class="erp-input w-full" required></div>
            <div><label class="erp-label"><?php echo e(__('Waste quantity')); ?></label><input type="number" step="0.001" name="waste_quantity" class="erp-input w-full" value="0"></div>
            <div>
                <label class="erp-label"><?php echo e(__('Waste reason')); ?></label>
                <select name="waste_reason" class="erp-input w-full">
                    <option value=""><?php echo e(__('—')); ?></option>
                    <?php $__currentLoopData = $tabData['waste_reasons'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($reason->value); ?>"><?php echo e($reason->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="md:col-span-2 lg:col-span-3">
                <label class="erp-label"><?php echo e(__('Notes')); ?></label>
                <textarea name="notes" class="erp-input w-full" rows="2"></textarea>
            </div>
            <?php if(($tabData['can_capture_materials'] ?? false) && ($tabData['material_requirements'] ?? collect())->isNotEmpty()): ?>
                <div class="md:col-span-2 lg:col-span-3">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600"><?php echo e(__('Material consumption')); ?></h4>
                    <div class="overflow-x-auto">
                        <table class="erp-table w-full text-sm">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Material')); ?></th>
                                    <th><?php echo e(__('Consumed')); ?></th>
                                    <th><?php echo e(__('Waste')); ?></th>
                                    <th><?php echo e(__('Returned')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $tabData['material_requirements']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <?php echo e($row['item_name']); ?>

                                            <input type="hidden" name="materials[<?php echo e($index); ?>][production_material_requirement_id]" value="<?php echo e($row['requirement']->id); ?>">
                                            <input type="hidden" name="materials[<?php echo e($index); ?>][inventory_item_id]" value="<?php echo e($row['requirement']->inventory_item_id); ?>">
                                            <input type="hidden" name="materials[<?php echo e($index); ?>][warehouse_id]" value="<?php echo e($row['requirement']->warehouse_id); ?>">
                                        </td>
                                        <td><input type="number" step="0.001" min="0" name="materials[<?php echo e($index); ?>][consumed_quantity]" class="erp-input w-24 text-sm" value="0"></td>
                                        <td><input type="number" step="0.001" min="0" name="materials[<?php echo e($index); ?>][waste_quantity]" class="erp-input w-24 text-sm" value="0"></td>
                                        <td><input type="number" step="0.001" min="0" name="materials[<?php echo e($index); ?>][returned_quantity]" class="erp-input w-24 text-sm" value="0"></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            <div><button type="submit" class="erp-btn-primary"><?php echo e(__('Save session')); ?></button></div>
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
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Production Sessions')); ?></h3>
    <?php if($sessions && $sessions->count() > 0): ?>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Operator')); ?></th>
                        <th><?php echo e(__('Start')); ?></th>
                        <th><?php echo e(__('End')); ?></th>
                        <th><?php echo e(__('Expected')); ?></th>
                        <th><?php echo e(__('Produced')); ?></th>
                        <th><?php echo e(__('Waste')); ?></th>
                        <th><?php echo e(__('Reason')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($session->operator?->name ?? '—'); ?></td>
                            <td class="tabular-nums"><?php echo e($session->started_at?->format('Y-m-d H:i')); ?></td>
                            <td class="tabular-nums"><?php echo e($session->ended_at?->format('Y-m-d H:i') ?? '—'); ?></td>
                            <td class="tabular-nums"><?php echo e(number_format($session->expected_quantity, 0)); ?></td>
                            <td class="tabular-nums"><?php echo e(number_format($session->produced_quantity, 0)); ?></td>
                            <td class="tabular-nums text-red-700"><?php echo e(number_format($session->waste_quantity, 0)); ?></td>
                            <td><?php echo e($session->waste_reason?->label() ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php if($sessions->hasPages()): ?><div class="mt-4"><?php echo e($sessions->links()); ?></div><?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No sessions recorded')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No sessions recorded'))]); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\sessions.blade.php ENDPATH**/ ?>