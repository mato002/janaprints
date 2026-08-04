<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value' => null, 'categories' => [], 'selectedCategory' => null, 'showCategory' => true]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['value' => null, 'categories' => [], 'selectedCategory' => null, 'showCategory' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="space-y-4">
    <?php if($showCategory): ?>
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Category')); ?></label>
            <select name="category_key" class="erp-input mt-1 w-full" required <?php if($value): echo 'disabled'; endif; ?>>
                <option value=""><?php echo e(__('Select category')); ?></option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($option['value']); ?>" <?php if(old('category_key', $value?->category_key ?? $selectedCategory) === $option['value']): echo 'selected'; endif; ?>>
                        <?php echo e($option['module']); ?> · <?php echo e($option['label']); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <?php if (! ($value)): ?>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Code')); ?></label>
                <input type="text" name="code" value="<?php echo e(old('code')); ?>" class="erp-input mt-1 w-full" placeholder="<?php echo e(__('Auto-generated')); ?>" maxlength="80" />
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Name')); ?></label>
        <input type="text" name="name" value="<?php echo e(old('name', $value?->name)); ?>" class="erp-input mt-1 w-full" required maxlength="255" />
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Description')); ?></label>
        <textarea name="description" rows="3" class="erp-input mt-1 w-full"><?php echo e(old('description', $value?->description)); ?></textarea>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Sort order')); ?></label>
            <input type="number" name="sort_order" value="<?php echo e(old('sort_order', $value?->sort_order ?? 0)); ?>" class="erp-input mt-1 w-full" min="0" max="9999" />
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="hidden" name="is_active" value="0" />
                <input type="checkbox" name="is_active" value="1" class="rounded border-erp-border text-erp-accent" <?php if(old('is_active', $value?->is_active ?? true)): echo 'checked'; endif; ?> />
                <?php echo e(__('Active')); ?>

            </label>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\master-data\partials\form.blade.php ENDPATH**/ ?>