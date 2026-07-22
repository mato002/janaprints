<?php ($m = $unit ?? null); ?>
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="erp-label"><?php echo e(__('Code')); ?></label>
        <input type="text" name="code" class="erp-input w-full" value="<?php echo e(old('code', $m?->code)); ?>" required maxlength="50">
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Name')); ?></label>
        <input type="text" name="name" class="erp-input w-full" value="<?php echo e(old('name', $m?->name)); ?>" required maxlength="255">
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Base unit')); ?></label>
        <select name="base_unit_id" class="erp-select w-full">
            <option value=""><?php echo e(__('This is a base unit')); ?></option>
            <?php $__currentLoopData = $baseUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $baseUnit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($baseUnit->id); ?>" <?php if((string) old('base_unit_id', $m?->base_unit_id) === (string) $baseUnit->id): echo 'selected'; endif; ?>><?php echo e($baseUnit->name); ?> (<?php echo e($baseUnit->code); ?>)</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Conversion factor')); ?></label>
        <input type="number" step="0.0001" min="0.0001" name="conversion_factor" class="erp-input w-full" value="<?php echo e(old('conversion_factor', $m?->conversion_factor ?? 1)); ?>">
        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Example: 1 Ream = 500 Sheets')); ?></p>
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" <?php if(old('is_active', $m?->is_active ?? true)): echo 'checked'; endif; ?>>
            <?php echo e(__('Active')); ?>

        </label>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\catalogue\units\partials\form.blade.php ENDPATH**/ ?>