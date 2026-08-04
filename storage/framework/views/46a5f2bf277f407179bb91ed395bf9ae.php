<?php ($m = $unit ?? null); ?>
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="erp-label"><?php echo e(__('Name')); ?></label>
        <input type="text" name="name" class="erp-input w-full" value="<?php echo e(old('name', $m?->name)); ?>" required maxlength="255">
    </div>
    <div>
        <?php if (isset($component)) { $__componentOriginal6da14397ddf3530b276d246dba7e4584 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6da14397ddf3530b276d246dba7e4584 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.entity-code-input','data' => ['record' => $m,'erp' => true,'maxlength' => '50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.entity-code-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m),'erp' => true,'maxlength' => '50']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6da14397ddf3530b276d246dba7e4584)): ?>
<?php $attributes = $__attributesOriginal6da14397ddf3530b276d246dba7e4584; ?>
<?php unset($__attributesOriginal6da14397ddf3530b276d246dba7e4584); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6da14397ddf3530b276d246dba7e4584)): ?>
<?php $component = $__componentOriginal6da14397ddf3530b276d246dba7e4584; ?>
<?php unset($__componentOriginal6da14397ddf3530b276d246dba7e4584); ?>
<?php endif; ?>
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