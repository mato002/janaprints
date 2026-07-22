<?php ($m = $priceList ?? null); ?>
<?php ($priceLines = isset($m) ? $m->items->values() : collect()); ?>
<div class="erp-form-grid">
    <div><label class="erp-label"><?php echo e(__('Name')); ?></label><input name="name" class="erp-input w-full" value="<?php echo e(old('name', $m?->name)); ?>" required></div>
    <div><label class="erp-label"><?php echo e(__('Currency')); ?></label><input name="currency" maxlength="3" class="erp-input w-full uppercase" value="<?php echo e(old('currency', $m?->currency ?? 'KES')); ?>" required></div>
    <div><label class="erp-label"><?php echo e(__('Effective date')); ?></label><input type="date" name="effective_date" class="erp-input w-full" value="<?php echo e(old('effective_date', $m?->effective_date?->toDateString())); ?>"></div>
    <div><label class="erp-label"><?php echo e(__('Status')); ?></label><select name="status" class="erp-select w-full" required><?php $__currentLoopData = ['active' => 'Active', 'draft' => 'Draft', 'inactive' => 'Inactive']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('status', $m?->status ?? 'active') === $value): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
</div>
<div class="mt-4">
    <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Items')); ?></h3>
    <div class="space-y-2">
        <?php for($i = 0; $i < 8; $i++): ?>
            <?php ($line = $priceLines->get($i)); ?>
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                <select name="items[<?php echo e($i); ?>][inventory_item_id]" class="erp-select w-full">
                    <option value=""><?php echo e(__('Select item')); ?></option>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($item->id); ?>" <?php if(old("items.$i.inventory_item_id", $line?->inventory_item_id) == $item->id): echo 'selected'; endif; ?>><?php echo e($item->sku); ?> - <?php echo e($item->item_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="number" step="0.01" min="0" name="items[<?php echo e($i); ?>][price_override]" class="erp-input w-full" placeholder="<?php echo e(__('Price override')); ?>" value="<?php echo e(old('items.'.$i.'.price_override', $line?->price_override)); ?>">
            </div>
        <?php endfor; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\catalogue\price-lists\partials\form.blade.php ENDPATH**/ ?>