<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'slotKey' => null,
    'slot_key' => null,
    'src' => null,
    'fallbackKey' => null,
    'fallback_key' => null,
    'fallback' => 'default',
    'alt' => '',
    'class' => '',
    'width' => null,
    'height' => null,
    'sizes' => null,
    'loading' => 'lazy',
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
    'slotKey' => null,
    'slot_key' => null,
    'src' => null,
    'fallbackKey' => null,
    'fallback_key' => null,
    'fallback' => 'default',
    'alt' => '',
    'class' => '',
    'width' => null,
    'height' => null,
    'sizes' => null,
    'loading' => 'lazy',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $resolver = app(\App\Services\Website\WebsiteMediaResolver::class);
    $slot = $slot_key ?? $slotKey;
    $fallbackSlot = $fallback_key ?? $fallbackKey ?? $fallback ?? 'default';

    if ($slot) {
        $resolved = $resolver->resolvePath((string) $slot);
        $resolvedAlt = $alt !== '' ? $alt : $resolver->resolveAlt((string) $slot);
    } elseif ($src !== null && $src !== '') {
        $resolved = $resolver->resolveSource((string) $src, (string) $fallbackSlot);
        $resolvedAlt = $alt !== '' ? $alt : $resolver->resolveAltForSource((string) $src, '', '');
    } else {
        $resolved = $resolver->resolvePath((string) $fallbackSlot);
        $resolvedAlt = $alt;
    }

    $fallbackUrl = $resolver->resolvePath((string) $fallbackSlot);
    $loadingAttr = in_array($loading, ['eager', 'lazy'], true) ? $loading : 'lazy';
?>

<img
    src="<?php echo e($resolved); ?>"
    alt="<?php echo e($resolvedAlt); ?>"
    <?php if($width): ?> width="<?php echo e($width); ?>" <?php endif; ?>
    <?php if($height): ?> height="<?php echo e($height); ?>" <?php endif; ?>
    <?php if($sizes): ?> sizes="<?php echo e($sizes); ?>" <?php endif; ?>
    loading="<?php echo e($loadingAttr); ?>"
    decoding="async"
    <?php if($slot): ?> data-website-media-slot="<?php echo e($slot); ?>" <?php endif; ?>
    data-public-media-image
    <?php echo e($attributes->merge(['class' => trim($class)])); ?>

    onerror="if(!this.dataset.fallbackApplied){this.dataset.fallbackApplied='1';this.src='<?php echo e($fallbackUrl); ?>';}"
>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/media-image.blade.php ENDPATH**/ ?>