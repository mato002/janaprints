<?php ($m = $code ?? null); ?>
<div><?php if (isset($component)) { $__componentOriginal6da14397ddf3530b276d246dba7e4584 = $component; } ?>
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
<?php endif; ?></div>
<div><label class="erp-label"><?php echo e(__('Name')); ?></label><input name="name" class="erp-input w-full" value="<?php echo e(old('name', $m?->name)); ?>" required maxlength="255"></div>
<div>
    <label class="erp-label"><?php echo e(__('Category')); ?></label>
    <select name="category" class="erp-select w-full" required>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($category->value); ?>" <?php if(old('category', $m?->category?->value) === $category->value): echo 'selected'; endif; ?>><?php echo e($category->label()); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>
<label class="inline-flex items-center gap-2 text-sm">
    <input type="hidden" name="requires_comment" value="0">
    <input type="checkbox" name="requires_comment" value="1" <?php if(old('requires_comment', $m?->requires_comment ?? true)): echo 'checked'; endif; ?>>
    <span><?php echo e(__('Comment required when used')); ?></span>
</label>
<label class="inline-flex items-center gap-2 text-sm">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $m?->is_active ?? true)): echo 'checked'; endif; ?>>
    <span><?php echo e(__('Active')); ?></span>
</label>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\control\variance-reason-codes\partials\form-fields.blade.php ENDPATH**/ ?>