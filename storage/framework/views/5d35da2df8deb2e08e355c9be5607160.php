<?php ($m = $brand ?? null); ?>
<div class="erp-form-grid">
    <div><label class="erp-label"><?php echo e(__('Code')); ?></label><input name="code" class="erp-input w-full" value="<?php echo e(old('code', $m?->code)); ?>" required></div>
    <div><label class="erp-label"><?php echo e(__('Name')); ?></label><input name="name" class="erp-input w-full" value="<?php echo e(old('name', $m?->name)); ?>" required></div>
    <div class="md:col-span-2"><label class="erp-label"><?php echo e(__('Logo')); ?></label><input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp" class="erp-input w-full"></div>
    <div class="md:col-span-2"><label class="erp-label"><?php echo e(__('Description')); ?></label><textarea name="description" class="erp-input w-full" rows="3"><?php echo e(old('description', $m?->description)); ?></textarea></div>
    <div><input type="hidden" name="is_active" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $m?->is_active ?? true)): echo 'checked'; endif; ?>><span><?php echo e(__('Active')); ?></span></label></div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\catalogue\brands\partials\form.blade.php ENDPATH**/ ?>