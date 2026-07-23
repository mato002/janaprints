<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'padding' => true,
    'hover' => false,
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
    'padding' => true,
    'hover' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge([
    'class' => 'rounded-lg border border-erp-border bg-erp-card shadow-card transition-shadow duration-200'
        .($hover ? ' hover:shadow-card-hover' : '')
        .($padding ? ' p-4' : ''),
])); ?>>
    <?php if(isset($header)): ?>
        <div class="border-b border-erp-border px-4 py-3 <?php echo e($padding ? '' : ''); ?>">
            <?php echo e($header); ?>

        </div>
    <?php endif; ?>

    <?php if(isset($body)): ?>
        <div class="<?php echo e(isset($header) || isset($footer) ? 'px-4 py-3' : ''); ?>">
            <?php echo e($body); ?>

        </div>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?>

    <?php if(isset($footer)): ?>
        <div class="border-t border-erp-border px-4 py-3 bg-erp-page/50 rounded-b-lg">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\card.blade.php ENDPATH**/ ?>