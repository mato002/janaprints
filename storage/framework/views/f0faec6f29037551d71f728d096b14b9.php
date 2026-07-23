<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description',
    'icon' => 'cog',
    'href' => null,
    'status' => null,
    'statusLabel' => null,
    'statusDetail' => null,
    'statusVariant' => 'neutral',
    'comingSoon' => false,
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
    'description',
    'icon' => 'cog',
    'href' => null,
    'status' => null,
    'statusLabel' => null,
    'statusDetail' => null,
    'statusVariant' => 'neutral',
    'comingSoon' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($comingSoon || empty($href)): ?>
    <div
        <?php echo e($attributes->merge(['class' => 'group relative flex flex-col rounded-xl border border-dashed border-erp-border bg-erp-card/60 p-5 opacity-75'])); ?>

        aria-disabled="true"
    >
        <?php echo $__env->make('admin.settings.partials.control-center-card-inner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php else: ?>
    <a
        href="<?php echo e($href); ?>"
        data-turbo-action="advance"
        <?php echo e($attributes->merge(['class' => 'group relative flex flex-col rounded-xl border border-erp-border bg-erp-card p-5 shadow-card transition-all duration-200 hover:border-erp-accent/40 hover:shadow-card-hover focus:outline-none focus:ring-2 focus:ring-erp-accent focus:ring-offset-2'])); ?>

    >
        <?php echo $__env->make('admin.settings.partials.control-center-card-inner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\control-center-card.blade.php ENDPATH**/ ?>