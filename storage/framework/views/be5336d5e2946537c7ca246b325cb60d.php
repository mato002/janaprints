<?php
    $consumptions = $tabData['consumptions'] ?? null;
    $wastage = $tabData['wastage'] ?? [];
    $sessionWaste = $tabData['session_waste'] ?? [];
    $serialSpoilage = $tabData['serial_spoilage'] ?? [];
    $requirements = collect($tabData['material_requirements'] ?? []);
    $defaultRequirement = $requirements->first(fn ($row) => ($row['remaining'] ?? 0) > 0) ?? $requirements->first();
    $defaultItemId = old('inventory_item_id', $defaultRequirement['requirement']->inventory_item_id ?? null);
    $defaultWarehouseId = old('warehouse_id', $defaultRequirement['requirement']->warehouse_id ?? ($tabData['warehouses'][0]->id ?? null));
    $defaultQty = old('quantity', $defaultRequirement['remaining'] ?? null);
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

<?php if($tabData['can_record_waste'] ?? false): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4','id' => 'record-waste']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4','id' => 'record-waste']); ?>
        <h3 class="mb-1 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Record waste')); ?></h3>
        <p class="mb-3 text-xs text-slate-500"><?php echo e(__('Capture production waste against this job for costing and yield tracking.')); ?></p>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.wastage.store', $jobCard)); ?>" class="grid grid-cols-1 gap-2 md:grid-cols-3 lg:grid-cols-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="flow_type" value="waste">
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Material')); ?></label>
                <select name="inventory_item_id" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = $tabData['inventory_items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($inv->id); ?>" <?php if((string) $defaultItemId === (string) $inv->id): echo 'selected'; endif; ?>><?php echo e($inv->sku); ?> — <?php echo e($inv->item_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Warehouse')); ?></label>
                <select name="warehouse_id" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = $tabData['warehouses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($wh->id); ?>" <?php if((string) $defaultWarehouseId === (string) $wh->id): echo 'selected'; endif; ?>><?php echo e($wh->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Waste type')); ?></label>
                <select name="waste_type" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = \App\Enums\ProductionWasteType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->value); ?>"><?php echo e($type->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Quantity')); ?></label>
                <input type="number" step="0.001" name="quantity" class="erp-input w-full text-sm" value="<?php echo e(old('quantity', $defaultQty)); ?>" required>
            </div>
            <div class="md:col-span-3 lg:col-span-4 flex justify-end">
                <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Record waste')); ?></button>
            </div>
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

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4','id' => 'record-return']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4','id' => 'record-return']); ?>
        <h3 class="mb-1 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Record material return')); ?></h3>
        <p class="mb-3 text-xs text-slate-500"><?php echo e(__('Return unused material from the shop floor back to stock.')); ?></p>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.wastage.store', $jobCard)); ?>" class="grid grid-cols-1 gap-2 md:grid-cols-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="flow_type" value="return">
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Material')); ?></label>
                <select name="inventory_item_id" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = $tabData['inventory_items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($inv->id); ?>"><?php echo e($inv->sku); ?> — <?php echo e($inv->item_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Warehouse')); ?></label>
                <select name="warehouse_id" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = $tabData['warehouses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Quantity')); ?></label>
                <input type="number" step="0.001" name="quantity" class="erp-input w-full text-sm" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="erp-btn-secondary w-full text-sm"><?php echo e(__('Record return')); ?></button>
            </div>
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

<?php if($tabData['can_consume'] ?? false): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4','id' => 'consume-material']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4','id' => 'consume-material']); ?>
        <h3 class="mb-1 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Record consumption')); ?></h3>
        <p class="mb-3 text-xs text-slate-500"><?php echo e(__('Deduct raw materials from a physical warehouse. When this job has material requirements, consumption counts toward that requirement and cannot exceed the remaining quantity.')); ?></p>
        <?php if($requirements->contains(fn ($row) => ($row['remaining'] ?? 0) > 0)): ?>
            <p class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"><?php echo e(__('Prefer the Consume button on the Materials tab for each requirement line. Manual entry here is capped to the same remaining quantity.')); ?></p>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('admin.inventory.production.consume', $jobCard)); ?>" class="grid grid-cols-1 gap-2 md:grid-cols-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Material')); ?></label>
                <select name="inventory_item_id" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = $tabData['inventory_items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($inv->id); ?>" <?php if((string) $defaultItemId === (string) $inv->id): echo 'selected'; endif; ?>><?php echo e($inv->sku); ?> — <?php echo e($inv->item_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Physical warehouse')); ?></label>
                <select name="warehouse_id" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = $tabData['warehouses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($wh->id); ?>" <?php if((string) $defaultWarehouseId === (string) $wh->id): echo 'selected'; endif; ?>><?php echo e($wh->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Quantity')); ?></label>
                <input type="number" step="0.001" name="quantity" class="erp-input w-full text-sm" value="<?php echo e($defaultQty); ?>" placeholder="<?php echo e(__('Qty')); ?>" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="erp-btn-primary w-full text-sm"><?php echo e(__('Record')); ?></button>
            </div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No consumption recorded'),'description' => __('Consumption is captured during production sessions or manually from requirements.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No consumption recorded')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Consumption is captured during production sessions or manually from requirements.'))]); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\material-consumption.blade.php ENDPATH**/ ?>