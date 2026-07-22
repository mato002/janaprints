<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description' => null,
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
    'title',
    'description' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'compact-workspace-header'])); ?>>
    <div class="compact-workspace-header__row">
        <div class="compact-workspace-header__title-group min-w-0">
            <h1 class="compact-workspace-header__title truncate"><?php echo e($title); ?></h1>
            <?php if($description): ?>
                <p class="compact-workspace-header__description"><?php echo e($description); ?></p>
            <?php endif; ?>
        </div>

        <?php if(isset($search)): ?>
            <div class="compact-workspace-header__search relative shrink-0">
                <?php echo e($search); ?>

            </div>
        <?php endif; ?>

        <?php if(isset($actions)): ?>
            <div class="compact-workspace-header__actions shrink-0">
                <?php echo e($actions); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\compact-workspace-header.blade.php ENDPATH**/ ?>