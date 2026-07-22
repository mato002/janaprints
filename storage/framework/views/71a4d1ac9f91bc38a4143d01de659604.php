<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'description' => null]));

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

foreach (array_filter((['title', 'description' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section <?php echo e($attributes->merge(['class' => 'space-y-5'])); ?>>
    <div>
        <h3 class="text-sm font-semibold text-erp-primary"><?php echo e($title); ?></h3>
        <?php if($description): ?>
            <p class="mt-1 text-sm text-slate-500"><?php echo e($description); ?></p>
        <?php endif; ?>
    </div>
    <div class="erp-form-grid">
        <?php echo e($slot); ?>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\form-section.blade.php ENDPATH**/ ?>