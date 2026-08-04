<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['area', 'reversed' => false]));

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

foreach (array_filter((['area', 'reversed' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-facility-area', 'public-facility-area--reversed' => $reversed]); ?>" data-animate="fade-up">
    <div class="public-facility-area__visual">
        <div class="public-image-reveal" data-image-reveal>
            <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $area['image'],'alt' => $area['alt'],'fallback' => 'production_floor','width' => '1000','height' => '667','class' => 'h-full w-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($area['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($area['alt']),'fallback' => 'production_floor','width' => '1000','height' => '667','class' => 'h-full w-full object-cover']); ?>
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
        </div>
    </div>
    <div class="public-facility-area__content">
        <h4 class="public-facility-area__title"><?php echo e($area['title']); ?></h4>
        <p class="public-facility-area__desc"><?php echo e($area['description']); ?></p>
    </div>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\facility-area.blade.php ENDPATH**/ ?>