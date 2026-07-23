<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value',
    'percent' => null,
    'variant' => 'default',
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
    'percent' => null,
    'variant' => 'default',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $barClass = match ($variant) {
        'success' => 'exec-progress__bar--success',
        'warning' => 'exec-progress__bar--warning',
        'danger' => 'exec-progress__bar--danger',
        default => '',
    };
    $pct = $percent !== null ? min(100, max(0, (int) $percent)) : null;
?>

<div <?php echo e($attributes->merge(['class' => 'exec-progress-widget'])); ?>>
    <div class="exec-progress-widget__head">
        <span class="exec-progress-widget__label"><?php echo e($label); ?></span>
        <span class="exec-progress-widget__value"><?php echo e($value); ?></span>
    </div>
    <?php if($pct !== null): ?>
        <div class="exec-progress__track" role="progressbar" aria-valuenow="<?php echo e($pct); ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="exec-progress__bar <?php echo e($barClass); ?>" style="width: <?php echo e($pct); ?>%"></div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/exec-progress-widget.blade.php ENDPATH**/ ?>