<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'hint' => null,
    'tone' => 'accent',
    'when' => null,
    'reasons' => [],
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
    'hint' => null,
    'tone' => 'accent',
    'when' => null,
    'reasons' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="rw-nba rw-nba--<?php echo e($tone); ?>" aria-label="<?php echo e(__('Next best action')); ?>">
    <div class="rw-nba__main">
        <p class="rw-nba__eyebrow">
            <?php echo e($when ?? __('Today')); ?>

        </p>
        <p class="rw-nba__title"><?php echo e($label); ?></p>
        <?php if($hint): ?>
            <p class="rw-nba__hint"><?php echo e($hint); ?></p>
        <?php endif; ?>
    </div>

    <?php if($reasons !== []): ?>
        <ul class="rw-nba__reasons" role="list">
            <?php $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($reason); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>

    <?php if(isset($cta)): ?>
        <div class="rw-nba__cta"><?php echo e($cta); ?></div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\record-workspace\next-action.blade.php ENDPATH**/ ?>