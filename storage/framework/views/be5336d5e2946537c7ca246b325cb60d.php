<?php
    $consumptions = $tabData['consumptions'] ?? null;
    $wastage = $tabData['wastage'] ?? [];
    $sessionWaste = $tabData['session_waste'] ?? [];
    $serialSpoilage = $tabData['serial_spoilage'] ?? [];
    $canConsume = (bool) ($tabData['can_consume'] ?? false);
    $canRecordWaste = (bool) ($tabData['can_record_waste'] ?? false);
?>

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
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Waste consolidation')); ?></h3>
    <dl class="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
        <div><dt class="text-slate-500"><?php echo e(__('Material waste')); ?></dt><dd class="tabular-nums"><?php echo e($wastage['metrics']['material_wasted'] ?? 0); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Session waste')); ?></dt><dd class="tabular-nums"><?php echo e($sessionWaste['total_waste'] ?? 0); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Serial spoilage')); ?></dt><dd class="tabular-nums"><?php echo e($serialSpoilage['spoiled_quantity'] ?? 0); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Returned')); ?></dt><dd class="tabular-nums"><?php echo e($wastage['metrics']['material_returned'] ?? 0); ?></dd></div>
    </dl>
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

<?php if($canConsume || $canRecordWaste): ?>
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
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Material transactions')); ?></h3>
                <p class="text-sm text-slate-600"><?php echo e(__('Record consumption, waste, or returns against this job.')); ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if($canConsume): ?>
                    <button type="button" class="erp-btn-primary text-sm" data-open-dialog="record-consumption-modal"><?php echo e(__('Record consumption')); ?></button>
                <?php endif; ?>
                <?php if($canRecordWaste): ?>
                    <button type="button" class="erp-btn-secondary text-sm" data-open-dialog="record-waste-modal"><?php echo e(__('Record waste')); ?></button>
                    <button type="button" class="erp-btn-secondary text-sm" data-open-dialog="record-return-modal"><?php echo e(__('Record return')); ?></button>
                <?php endif; ?>
            </div>
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
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Consumption history')); ?></h3>
    <?php if($consumptions && $consumptions->count() > 0): ?>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Item')); ?></th>
                        <th><?php echo e(__('Qty')); ?></th>
                        <th><?php echo e(__('Unit')); ?></th>
                        <th><?php echo e(__('Cost')); ?></th>
                        <th><?php echo e(__('Warehouse')); ?></th>
                        <th><?php echo e(__('By')); ?></th>
                        <th><?php echo e(__('At')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $consumptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($row->inventoryItem?->item_name); ?> <span class="text-slate-500">(<?php echo e($row->inventoryItem?->sku); ?>)</span></td>
                            <td class="tabular-nums"><?php echo e($row->quantity); ?></td>
                            <td><?php echo e($row->inventoryItem?->unitOfMeasure?->code ?? '—'); ?></td>
                            <td class="tabular-nums"><?php echo e($row->unit_cost !== null ? number_format((float) $row->quantity * (float) $row->unit_cost, 2) : '—'); ?></td>
                            <td><?php echo e($row->warehouse?->name ?? '—'); ?></td>
                            <td><?php echo e($row->consumer?->name ?? '—'); ?></td>
                            <td><?php echo e($row->consumed_at?->format('Y-m-d H:i')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php if($consumptions->hasPages()): ?>
            <div class="mt-4"><?php echo e($consumptions->links()); ?></div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No consumption recorded'),'description' => __('Use Record consumption to deduct materials from stock for this job.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No consumption recorded')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Use Record consumption to deduct materials from stock for this job.'))]); ?>
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

<?php echo $__env->make('admin.production.job-cards.workspace.partials.material-consumption-modals', [
    'jobCard' => $jobCard,
    'tabData' => $tabData,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\material-consumption.blade.php ENDPATH**/ ?>