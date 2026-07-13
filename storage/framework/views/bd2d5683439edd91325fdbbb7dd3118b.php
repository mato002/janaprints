<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label',
    'value' => null,
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
    'name',
    'label',
    'value' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isActive = filled($value);
?>

<label class="erp-filter-pill-field relative inline-flex shrink-0">
    <span class="sr-only"><?php echo e($label); ?></span>
    <input
        type="date"
        name="<?php echo e($name); ?>"
        value="<?php echo e($value); ?>"
        aria-label="<?php echo e($label); ?>"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'erp-filter-pill-date',
            'erp-filter-pill-date--active' => $isActive,
        ]); ?>"
        <?php echo e($attributes->except(['name', 'label', 'value'])); ?>

    />
</label>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/filter-pill-date.blade.php ENDPATH**/ ?>