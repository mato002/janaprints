<?php ($bom = $bom ?? null); ?>
<?php ($lineRows = old('lines', $bom ? $bom->lines->map(fn ($line) => [
    'inventory_item_id' => $line->inventory_item_id,
    'quantity_per_unit' => $line->quantity_per_unit,
    'waste_factor_percent' => $line->waste_factor_percent,
    'notes' => $line->notes,
])->all() : [['inventory_item_id' => '', 'quantity_per_unit' => '', 'waste_factor_percent' => 0, 'notes' => '']])); ?>)

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
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="erp-label"><?php echo e(__('Finished product')); ?></label>
            <select name="finished_item_id" class="erp-input" required <?php if($bom): echo 'disabled'; endif; ?>>
                <option value=""><?php echo e(__('Select product')); ?></option>
                <?php $__currentLoopData = $finishedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($item->id); ?>" <?php if((int) old('finished_item_id', $bom?->finished_item_id) === $item->id): echo 'selected'; endif; ?>><?php echo e($item->sku); ?> — <?php echo e($item->item_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="erp-label"><?php echo e(__('BOM name')); ?></label>
            <input type="text" name="name" class="erp-input" value="<?php echo e(old('name', $bom?->name ?? '')); ?>" required>
        </div>
        <div>
            <label class="erp-label"><?php echo e(__('Version')); ?></label>
            <input type="number" name="version" min="1" class="erp-input" value="<?php echo e(old('version', $bom?->version ?? 1)); ?>">
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded" <?php if(old('is_active', $bom?->is_active ?? true)): echo 'checked'; endif; ?>>
                <?php echo e(__('Active')); ?>

            </label>
        </div>
        <div class="md:col-span-2">
            <label class="erp-label"><?php echo e(__('Notes')); ?></label>
            <textarea name="notes" class="erp-input" rows="2"><?php echo e(old('notes', $bom?->notes ?? '')); ?></textarea>
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
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Raw materials')); ?></h3>
    <div class="space-y-3" id="bom-lines">
        <?php $__currentLoopData = $lineRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="grid grid-cols-1 gap-2 rounded border border-slate-200 p-3 md:grid-cols-5">
                <div class="md:col-span-2">
                    <label class="erp-label"><?php echo e(__('Material')); ?></label>
                    <select name="lines[<?php echo e($index); ?>][inventory_item_id]" class="erp-input" required>
                        <option value=""><?php echo e(__('Select material')); ?></option>
                        <?php $__currentLoopData = $rawMaterials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($material->id); ?>" <?php if((int) ($line['inventory_item_id'] ?? 0) === $material->id): echo 'selected'; endif; ?>><?php echo e($material->sku); ?> — <?php echo e($material->item_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Qty / unit')); ?></label>
                    <input type="number" step="0.0001" min="0.0001" name="lines[<?php echo e($index); ?>][quantity_per_unit]" class="erp-input" value="<?php echo e($line['quantity_per_unit'] ?? ''); ?>" required>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Waste %')); ?></label>
                    <input type="number" step="0.01" min="0" max="100" name="lines[<?php echo e($index); ?>][waste_factor_percent]" class="erp-input" value="<?php echo e($line['waste_factor_percent'] ?? 0); ?>">
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Notes')); ?></label>
                    <input type="text" name="lines[<?php echo e($index); ?>][notes]" class="erp-input" value="<?php echo e($line['notes'] ?? ''); ?>">
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <button type="button" class="erp-btn-secondary mt-3 text-sm" onclick="addBomLine()"><?php echo e(__('Add line')); ?></button>
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

<script>
    function addBomLine() {
        const container = document.getElementById('bom-lines');
        const index = container.children.length;
        const template = container.children[0]?.cloneNode(true);
        if (!template) return;
        template.querySelectorAll('[name]').forEach((input) => {
            input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
            if (input.tagName === 'SELECT') input.selectedIndex = 0;
            else input.value = input.name.includes('waste_factor_percent') ? '0' : '';
        });
        container.appendChild(template);
    }
</script>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\boms\_form.blade.php ENDPATH**/ ?>