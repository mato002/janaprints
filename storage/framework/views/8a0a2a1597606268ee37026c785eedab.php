<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'showLabel' => true,
    'compact' => false,
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
    'showLabel' => true,
    'compact' => false,
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
    $resolver = app(\App\Services\Website\WebsiteMediaResolver::class);
    $defaultImage = $resolver->resolvePath('default');
    $cards = [
        ['type' => 'business-cards', 'key' => 'cards', 'label' => 'Business Cards', 'alt' => 'Premium business cards'],
        ['type' => 'brochures', 'key' => 'brochure', 'label' => 'Brochures', 'alt' => 'Professional brochures'],
        ['type' => 'packaging', 'key' => 'packaging', 'label' => 'Packaging', 'alt' => 'Custom packaging'],
        ['type' => 'banners', 'key' => 'banner', 'label' => 'Roll-Up Banners', 'alt' => 'Roll-up banners'],
        ['type' => 'stationery', 'key' => 'stationery', 'label' => 'Corporate Stationery', 'alt' => 'Corporate stationery'],
        ['type' => 'promotional', 'key' => 'merchandise', 'label' => 'Branded Merchandise', 'alt' => 'Branded merchandise'],
        ['type' => 'large-format', 'key' => 'print_press', 'label' => 'Large Format Prints', 'alt' => 'Large format prints'],
    ];
    $layoutClasses = [
        'business-cards' => 'public-hero-showcase__card--main',
        'brochures' => 'public-hero-showcase__card--brochures',
        'packaging' => 'public-hero-showcase__card--packaging',
        'banners' => 'public-hero-showcase__card--banners',
        'stationery' => 'public-hero-showcase__card--stationery',
        'promotional' => 'public-hero-showcase__card--merchandise',
        'large-format' => 'public-hero-showcase__card--large-format',
    ];
    $delays = ['0s', '0.8s', '1.2s', '0.4s', '1.6s', '2s', '1s'];
    $stripCards = array_slice($cards, 0, 5);
?>


<div class="public-hero-strip lg:hidden" data-parallax="0.08" aria-hidden="true">
    <div class="public-hero-strip__track">
        <?php $__currentLoopData = $stripCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <figure class="public-hero-strip__item">
                <img
                    src="<?php echo e($resolver->resolvePath($card['key'])); ?>"
                    alt=""
                    loading="<?php echo e($index === 0 ? 'eager' : 'lazy'); ?>"
                    decoding="async"
                    width="160"
                    height="120"
                    onerror="if(!this.dataset.fallbackApplied){this.dataset.fallbackApplied='1';this.src='<?php echo e($defaultImage); ?>';}"
                >
                <?php if($showLabel): ?>
                    <figcaption><?php echo e($card['label']); ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>


<div <?php echo e($attributes->merge(['class' => 'public-hero-showcase max-lg:hidden'])); ?> data-parallax="0.15">
    <div class="public-hero-showcase__glow"></div>

    <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <figure
            class="public-hero-showcase__card <?php echo e($layoutClasses[$card['type']] ?? ''); ?>"
            style="animation-delay: <?php echo e($delays[$index] ?? '0s'); ?>;"
            data-image-type="<?php echo e($card['type']); ?>"
        >
            <img
                src="<?php echo e($resolver->resolvePath($card['key'])); ?>"
                alt="<?php echo e($resolver->resolveAlt($card['key']) ?: $card['alt']); ?>"
                loading="<?php echo e($index === 0 ? 'eager' : 'lazy'); ?>"
                decoding="async"
                <?php if($index === 0): ?> fetchpriority="high" <?php endif; ?>
                width="600"
                height="450"
                onerror="if(!this.dataset.fallbackApplied){this.dataset.fallbackApplied='1';this.src='<?php echo e($defaultImage); ?>';}"
            >
            <?php if($showLabel): ?>
                <figcaption class="public-hero-showcase__label"><?php echo e($card['label']); ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/hero-showcase.blade.php ENDPATH**/ ?>