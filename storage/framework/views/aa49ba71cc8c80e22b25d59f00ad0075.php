<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['video', 'slotKey' => null, 'slot_key' => null]));

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

foreach (array_filter((['video', 'slotKey' => null, 'slot_key' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article class="public-video-testimonial" data-animate="fade-up">
    <button type="button" class="public-video-testimonial__trigger" aria-label="Play video testimonial from <?php echo e($video['role']); ?> (coming soon)">
        <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['slotKey' => $slot_key ?? $slotKey,'src' => $video['thumbnail'],'alt' => $video['alt'],'fallbackKey' => 'corporate','class' => 'h-full w-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['slot-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($slot_key ?? $slotKey),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($video['thumbnail']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($video['alt']),'fallback-key' => 'corporate','class' => 'h-full w-full object-cover']); ?>
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
        <span class="public-video-testimonial__overlay"></span>
        <span class="public-video-testimonial__play">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </span>
    </button>
    <div class="public-video-testimonial__body">
        <p class="public-video-testimonial__quote">&ldquo;<?php echo e($video['quote']); ?>&rdquo;</p>
        <p class="public-video-testimonial__role"><?php echo e($video['role']); ?></p>
    </div>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\video-testimonial.blade.php ENDPATH**/ ?>