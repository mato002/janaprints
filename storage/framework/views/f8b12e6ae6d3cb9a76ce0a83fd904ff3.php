<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'class' => null,
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
    'class' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div <?php echo e($attributes->class(['rw', $class])); ?>>
    <?php if(isset($header)): ?>
        <div class="rw__chrome">
            <?php echo e($header); ?>

        </div>
    <?php endif; ?>

    <?php if(isset($workflow)): ?>
        <div class="rw__workflow-wrap">
            <?php echo e($workflow); ?>

        </div>
    <?php endif; ?>

    <?php if(isset($actions)): ?>
        <div class="rw__actions-wrap">
            <?php echo e($actions); ?>

        </div>
    <?php endif; ?>

    <div class="rw__body">
        <div class="rw__main">
            <?php echo e($main ?? $slot); ?>

        </div>

        <?php if(isset($rail)): ?>
            <aside class="rw__rail" aria-label="<?php echo e(__('Record intelligence')); ?>">
                <?php echo e($rail); ?>

            </aside>
        <?php endif; ?>
    </div>

    <?php if(isset($modals)): ?>
        <?php echo e($modals); ?>

    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\record-workspace\shell.blade.php ENDPATH**/ ?>