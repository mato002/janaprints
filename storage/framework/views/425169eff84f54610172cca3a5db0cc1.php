<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value',
    'suffix' => '',
    'prefix' => '',
    'duration' => 1750,
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
    'value',
    'suffix' => '',
    'prefix' => '',
    'duration' => 1750,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $formatted = $prefix.number_format((int) $value).$suffix;
?>

<span
    data-counter="<?php echo e((int) $value); ?>"
    <?php if($suffix !== ''): ?> data-counter-suffix="<?php echo e($suffix); ?>" <?php endif; ?>
    <?php if($prefix !== ''): ?> data-counter-prefix="<?php echo e($prefix); ?>" <?php endif; ?>
    data-counter-duration="<?php echo e($duration); ?>"
    <?php echo e($attributes); ?>

><?php echo e($formatted); ?></span>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/counter.blade.php ENDPATH**/ ?>