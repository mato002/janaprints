<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'tone' => 'default', // default | work | edit | muted
    'flush' => false,
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
    'tone' => 'default', // default | work | edit | muted
    'flush' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section <?php echo e($attributes->class([
    'rw-section',
    'rw-section--'.$tone,
    'rw-section--flush' => $flush,
])); ?>>
    <?php if($title || isset($actions)): ?>
        <div class="rw-section__head">
            <?php if($title): ?>
                <h2 class="rw-section__title"><?php echo e($title); ?></h2>
            <?php endif; ?>
            <?php if(isset($actions)): ?>
                <div class="rw-section__actions"><?php echo e($actions); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="rw-section__body">
        <?php echo e($slot); ?>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/record-workspace/section.blade.php ENDPATH**/ ?>