<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value',
    'hint' => null,
    'subtext' => null,
    'href' => null,
    'empty' => false,
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
    'hint' => null,
    'subtext' => null,
    'href' => null,
    'empty' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" data-turbo-frame="erp-main" data-turbo-action="advance" <?php echo e($attributes->merge(['class' => 'exec-hero-metric exec-hero-metric--link'])); ?>>
<?php else: ?>
    <div <?php echo e($attributes->merge(['class' => 'exec-hero-metric'])); ?>>
<?php endif; ?>
    <span class="exec-hero-metric__label"><?php echo e($label); ?></span>
    <span class="exec-hero-metric__value <?php if($empty): ?> exec-hero-metric__value--empty <?php endif; ?>"><?php echo e($value); ?></span>
    <?php if($subtext): ?>
        <span class="exec-hero-metric__subtext"><?php echo e($subtext); ?></span>
    <?php endif; ?>
    <?php if($hint): ?>
        <span class="exec-hero-metric__hint"><?php echo e($hint); ?></span>
    <?php endif; ?>
<?php if($href): ?>
    </a>
<?php else: ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/exec-hero-metric.blade.php ENDPATH**/ ?>