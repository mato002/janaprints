<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'profile' => null,
    'inkTypes' => [],
    'inventoryItems' => [],
]));

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

foreach (array_filter(([
    'profile' => null,
    'inkTypes' => [],
    'inventoryItems' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isEdit = $profile !== null;
    $prefix = $isEdit ? 'edit_'.$profile['id'] : 'create';
?>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="<?php echo e($prefix); ?>_name"><?php echo e(__('Name')); ?></label>
        <input id="<?php echo e($prefix); ?>_name" type="text" name="name" value="<?php echo e(old('name', $profile['name'] ?? '')); ?>" class="erp-input mt-1 w-full text-sm" required />
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="<?php echo e($prefix); ?>_ink_type"><?php echo e(__('Ink Type')); ?></label>
        <select id="<?php echo e($prefix); ?>_ink_type" name="ink_type" class="erp-select mt-1 w-full text-sm" required>
            <option value=""><?php echo e(__('Select ink type')); ?></option>
            <?php $__currentLoopData = $inkTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($type->value); ?>" <?php if(old('ink_type', $profile['ink_type_value'] ?? '') === $type->value): echo 'selected'; endif; ?>><?php echo e($type->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['ink_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="sm:col-span-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="<?php echo e($prefix); ?>_inventory_item_id"><?php echo e(__('Inventory Item')); ?></label>
        <select id="<?php echo e($prefix); ?>_inventory_item_id" name="inventory_item_id" class="erp-select mt-1 w-full text-sm">
            <option value=""><?php echo e(__('None')); ?></option>
            <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($item['id']); ?>" <?php if((string) old('inventory_item_id', $profile['inventory_item_id'] ?? '') === (string) $item['id']): echo 'selected'; endif; ?>><?php echo e($item['label']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['inventory_item_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="<?php echo e($prefix); ?>_cartridge_cost"><?php echo e(__('Cartridge Cost')); ?></label>
        <input id="<?php echo e($prefix); ?>_cartridge_cost" type="number" step="0.01" min="0" name="cartridge_cost" value="<?php echo e(old('cartridge_cost', $profile['cartridge_cost'] ?? '')); ?>" class="erp-input mt-1 w-full text-sm" required />
        <?php $__errorArgs = ['cartridge_cost'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="<?php echo e($prefix); ?>_estimated_ml"><?php echo e(__('Estimated ml')); ?></label>
        <input id="<?php echo e($prefix); ?>_estimated_ml" type="number" step="0.001" min="0" name="estimated_ml" value="<?php echo e(old('estimated_ml', $profile['estimated_ml'] ?? '')); ?>" class="erp-input mt-1 w-full text-sm" />
        <?php $__errorArgs = ['estimated_ml'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="<?php echo e($prefix); ?>_cost_per_ml"><?php echo e(__('Cost/ml (override)')); ?></label>
        <input id="<?php echo e($prefix); ?>_cost_per_ml" type="number" step="0.0001" min="0" name="cost_per_ml" value="<?php echo e(old('cost_per_ml', $profile['cost_per_ml_override'] ?? '')); ?>" class="erp-input mt-1 w-full text-sm" />
        <p class="mt-1 text-[11px] text-slate-500"><?php echo e(__('Leave blank to derive from cartridge cost ÷ estimated ml.')); ?></p>
        <?php $__errorArgs = ['cost_per_ml'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="<?php echo e($prefix); ?>_estimated_yield_pages"><?php echo e(__('Yield Pages')); ?></label>
        <input id="<?php echo e($prefix); ?>_estimated_yield_pages" type="number" min="0" name="estimated_yield_pages" value="<?php echo e(old('estimated_yield_pages', $profile['estimated_yield_pages'] ?? '')); ?>" class="erp-input mt-1 w-full text-sm" />
        <?php $__errorArgs = ['estimated_yield_pages'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="<?php echo e($prefix); ?>_estimated_yield_sq_m"><?php echo e(__('Yield m²')); ?></label>
        <input id="<?php echo e($prefix); ?>_estimated_yield_sq_m" type="number" step="0.001" min="0" name="estimated_yield_sq_m" value="<?php echo e(old('estimated_yield_sq_m', $profile['estimated_yield_sq_m'] ?? '')); ?>" class="erp-input mt-1 w-full text-sm" />
        <?php $__errorArgs = ['estimated_yield_sq_m'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="sm:col-span-2 flex items-center gap-2">
        <input type="hidden" name="active" value="0">
        <input id="<?php echo e($prefix); ?>_active" type="checkbox" name="active" value="1" class="rounded border-slate-300" <?php if(old('active', $profile['active'] ?? true)): echo 'checked'; endif; ?> />
        <label for="<?php echo e($prefix); ?>_active" class="text-sm text-slate-700"><?php echo e(__('Active')); ?></label>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\ink-profiles\partials\form-fields.blade.php ENDPATH**/ ?>