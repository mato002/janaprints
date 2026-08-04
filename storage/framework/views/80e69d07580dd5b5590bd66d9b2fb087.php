<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
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
    'title' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section <?php echo e($attributes->class(['rw-rail-card'])); ?>>
    <?php if($title || isset($actions)): ?>
        <div class="rw-rail-card__head">
            <?php if($title): ?>
                <h2 class="rw-rail-card__title"><?php echo e($title); ?></h2>
            <?php endif; ?>
            <?php if(isset($actions)): ?>
                <div><?php echo e($actions); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php echo e($slot); ?>

</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\record-workspace\rail-card.blade.php ENDPATH**/ ?>