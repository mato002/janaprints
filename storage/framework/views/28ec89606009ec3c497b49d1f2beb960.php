<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => 'md',
    'full' => false,
    'header' => false,
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
    'size' => 'md',
    'full' => false,
    'header' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $markSizeClasses = [
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-12 w-12',
    ];

    $fullSizeClasses = [
        'sm' => 'h-8 w-auto max-w-[150px]',
        'md' => 'h-10 w-auto max-w-[190px] sm:max-w-[210px]',
        'lg' => 'h-14 w-auto max-w-[240px] sm:max-w-[260px]',
    ];

    if ($header) {
        $classList = 'public-header__logo shrink-0 object-contain object-left';
    } elseif ($full) {
        $classList = ($fullSizeClasses[$size] ?? $fullSizeClasses['md']) . ' shrink-0 object-contain object-left';
    } else {
        $classList = ($markSizeClasses[$size] ?? $markSizeClasses['md']) . ' shrink-0 object-contain';
    }
?>

<img
    src="<?php echo e($brandingLogoUrl); ?>"
    alt="<?php echo e($full || $header ? config('site.name') : ''); ?>"
    <?php echo e($attributes->merge(['class' => $classList])); ?>

    <?php if($full || $header): ?>
        width="280"
        height="132"
    <?php else: ?>
        width="40"
        height="40"
    <?php endif; ?>
    decoding="async"
    <?php if(! $full && ! $header): ?>
        aria-hidden="true"
    <?php endif; ?>
>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\brand-logo.blade.php ENDPATH**/ ?>