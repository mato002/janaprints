<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value',
    'theme' => 'slate',
    'emphasis' => false,
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
    'label',
    'value',
    'theme' => 'slate',
    'emphasis' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->class([
    'job-360-kpi-tile',
    'job-360-kpi-tile--'.$theme,
    'job-360-kpi-tile--emphasis' => $emphasis,
])); ?>>
    <div class="job-360-kpi-tile__label"><?php echo e($label); ?></div>
    <div class="job-360-kpi-tile__value"><?php echo e($value); ?></div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\job-kpi-tile.blade.php ENDPATH**/ ?>