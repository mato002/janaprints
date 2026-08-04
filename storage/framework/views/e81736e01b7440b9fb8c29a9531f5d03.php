<?php ($m = $category ?? null); ?>
<div class="erp-form-grid">
    <div><label class="erp-label"><?php echo e(__('Name')); ?></label><input name="name" class="erp-input w-full" value="<?php echo e(old('name', $m?->name)); ?>" required></div>
    <?php if (isset($component)) { $__componentOriginal6da14397ddf3530b276d246dba7e4584 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6da14397ddf3530b276d246dba7e4584 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.entity-code-input','data' => ['record' => $m,'erp' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.entity-code-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m),'erp' => true]); ?>
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
    <div>
        <label class="erp-label"><?php echo e(__('Default UOM')); ?></label>
        <select name="default_uom_id" class="erp-select w-full">
            <option value=""><?php echo e(__('None')); ?></option>
            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($unit->id); ?>" <?php if(old('default_uom_id', $m?->default_uom_id) == $unit->id): echo 'selected'; endif; ?>><?php echo e($unit->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Reorder behavior')); ?></label>
        <select name="reorder_behavior" class="erp-select w-full" required>
            <?php $__currentLoopData = ['standard' => 'Standard', 'made_to_order' => 'Made to order', 'non_stock' => 'Non-stock', 'critical' => 'Critical']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('reorder_behavior', $m?->reorder_behavior ?? 'standard') === $value): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="md:col-span-2"><label class="erp-label"><?php echo e(__('Description')); ?></label><textarea name="description" class="erp-input w-full" rows="3"><?php echo e(old('description', $m?->description)); ?></textarea></div>
    <div class="md:col-span-2"><input type="hidden" name="is_active" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $m?->is_active ?? true)): echo 'checked'; endif; ?>><span><?php echo e(__('Active')); ?></span></label></div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\catalogue\categories\partials\form.blade.php ENDPATH**/ ?>