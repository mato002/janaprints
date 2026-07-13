<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'value', 'suffix' => null]));

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

foreach (array_filter((['label', 'value', 'suffix' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card transition-shadow duration-200 hover:shadow-card-hover">
    <p class="text-card-title text-erp-primary"><?php echo e($label); ?></p>
    <p class="mt-1.5 text-card-value text-erp-primary tabular-nums">
        <?php echo e($value); ?><?php if($suffix): ?><span class="ml-1 text-base font-medium text-slate-400"><?php echo e($suffix); ?></span><?php endif; ?>
    </p>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/stat-card.blade.php ENDPATH**/ ?>