<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value' => 0,
    'display' => null,
    'hint' => null,
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
    'value' => 0,
    'display' => null,
    'hint' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $pct = max(0, min(100, (int) $value));
?>

<div class="rw-meter">
    <div class="rw-meter__head">
        <span class="rw-meter__label"><?php echo e($label); ?></span>
        <span class="rw-meter__value"><?php echo e($display ?? $pct.'%'); ?></span>
    </div>
    <div class="rw-meter__track" aria-hidden="true">
        <span class="rw-meter__fill" style="width: <?php echo e($pct); ?>%"></span>
    </div>
    <?php if($hint): ?>
        <p class="rw-meter__hint"><?php echo e($hint); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\record-workspace\meter.blade.php ENDPATH**/ ?>