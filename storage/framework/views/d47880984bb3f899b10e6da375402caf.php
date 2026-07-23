<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['feature', 'reversed' => false]));

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

foreach (array_filter((['feature', 'reversed' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article
    id="why-<?php echo e($feature['slug']); ?>"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'public-why-feature',
        'public-why-feature--reversed' => $reversed,
        'public-why-feature--featured' => $feature['featured'] ?? false,
    ]); ?>"
    data-animate="fade-up"
>
    <div class="public-container">
        <div class="public-why-feature__grid">
            <div class="public-why-feature__visual" data-animate="<?php echo e($reversed ? 'fade-left' : 'fade-right'); ?>">
                <div class="public-why-feature__frame">
                    <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $feature['image'],'alt' => $feature['alt'],'fallback' => 'proof','class' => 'aspect-[4/3] w-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($feature['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($feature['alt']),'fallback' => 'proof','class' => 'aspect-[4/3] w-full object-cover']); ?>
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
                    <div class="public-why-feature__accent bg-gradient-to-br <?php echo e($feature['accent']); ?>"></div>
                </div>
                <span class="public-why-feature__float public-why-feature__float--number"><?php echo e($feature['number']); ?></span>
            </div>

            <div class="public-why-feature__content">
                <span class="public-why-feature__badge bg-gradient-to-r <?php echo e($feature['accent']); ?>">
                    Advantage <?php echo e($feature['number']); ?>

                </span>

                <h3 class="public-why-feature__title"><?php echo e($feature['title']); ?></h3>
                <p class="public-why-feature__desc"><?php echo e($feature['description']); ?></p>

                <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'public-why-feature__trust',
                    'public-why-feature__trust--featured' => $feature['featured'] ?? false,
                ]); ?>">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <?php echo e($feature['trust']); ?>

                </p>
            </div>
        </div>
    </div>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\why-feature-block.blade.php ENDPATH**/ ?>