<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'general',
    'label' => null,
    'aspect' => '4/3',
    'showLabel' => true,
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
    'type' => 'general',
    'label' => null,
    'aspect' => '4/3',
    'showLabel' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $images = config('public-images');

    $map = [
        'business-cards' => ['key' => 'cards', 'alt' => 'Premium business cards and corporate stationery'],
        'stationery' => ['key' => 'stationery', 'alt' => 'Corporate letterheads and branded stationery'],
        'packaging' => ['key' => 'packaging', 'alt' => 'Custom product packaging and branded boxes'],
        'brochures' => ['key' => 'brochure', 'alt' => 'Professional brochures and marketing collateral'],
        'flyers' => ['key' => 'prepress', 'alt' => 'Flyers and promotional print materials'],
        'banners' => ['key' => 'banner', 'alt' => 'Roll-up banners and exhibition displays'],
        'large-format' => ['key' => 'signage', 'alt' => 'Large format printing and signage'],
        'vehicle-branding' => ['key' => 'vehicle', 'alt' => 'Vehicle branding and fleet graphics'],
        'promotional' => ['key' => 'merchandise', 'alt' => 'Promotional materials and branded merchandise'],
        'design' => ['key' => 'artwork', 'alt' => 'Graphic design and pre-press services'],
        'general' => ['key' => 'default', 'alt' => 'Professional commercial printing'],
    ];

    $entry = $map[$type] ?? $map['general'];
    $displayLabel = $label ?? ucwords(str_replace('-', ' ', $type));
?>

<div
    <?php echo e($attributes->merge(['class' => 'public-image'])); ?>

    style="aspect-ratio: <?php echo e($aspect); ?>;"
    data-image-type="<?php echo e($type); ?>"
>
    <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $entry['key'],'alt' => $entry['alt'],'class' => 'h-full w-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entry['key']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entry['alt']),'class' => 'h-full w-full object-cover']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3a97f469f14669ba552e1c5a424bcd29)): ?>
<?php $attributes = $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29; ?>
<?php unset($__attributesOriginal3a97f469f14669ba552e1c5a424bcd29); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3a97f469f14669ba552e1c5a424bcd29)): ?>
<?php $component = $__componentOriginal3a97f469f14669ba552e1c5a424bcd29; ?>
<?php unset($__componentOriginal3a97f469f14669ba552e1c5a424bcd29); ?>
<?php endif; ?>
    <div class="public-image__overlay"></div>
    <?php if($showLabel): ?>
        <span class="public-image__label"><?php echo e($displayLabel); ?></span>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\image-placeholder.blade.php ENDPATH**/ ?>