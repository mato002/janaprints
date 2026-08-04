<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'project',
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
    'project',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article
    class="public-masonry-item public-portfolio-card"
    data-portfolio-item
    data-category="<?php echo e($project['category']); ?>"
    itemscope
    itemtype="https://schema.org/CreativeWork"
>
    <button
        type="button"
        class="public-masonry-item__trigger public-portfolio-card__trigger"
        data-portfolio-open
        data-project='<?php echo json_encode($project, 15, 512) ?>'
        aria-label="View project: <?php echo e($project['title']); ?>"
    >
        <div class="public-masonry-item__media public-portfolio-card__media">
            <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $project['image'],'alt' => $project['alt'],'fallbackKey' => 'cards','class' => 'public-masonry-item__image','width' => '480','sizes' => '(max-width: 480px) 45vw, (max-width: 640px) 33vw, (max-width: 1024px) 25vw, 20vw','itemprop' => 'image']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project['alt']),'fallback-key' => 'cards','class' => 'public-masonry-item__image','width' => '480','sizes' => '(max-width: 480px) 45vw, (max-width: 640px) 33vw, (max-width: 1024px) 25vw, 20vw','itemprop' => 'image']); ?>
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

            <div class="public-masonry-item__overlay public-portfolio-card__overlay" aria-hidden="true">
                <div class="public-masonry-item__overlay-content public-portfolio-card__overlay-content">
                    <span class="public-masonry-item__category public-portfolio-card__category public-portfolio-card__category--overlay" itemprop="genre">
                        <?php echo e($project['category_label']); ?>

                    </span>
                </div>
            </div>
        </div>
    </button>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\portfolio-card.blade.php ENDPATH**/ ?>