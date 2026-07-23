<?php ($m = $code ?? null); ?>
<div><label class="erp-label"><?php echo e(__('Code')); ?></label><input name="code" class="erp-input w-full" value="<?php echo e(old('code', $m?->code)); ?>" required maxlength="50"></div>
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