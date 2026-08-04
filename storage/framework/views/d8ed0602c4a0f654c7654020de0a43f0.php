<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'intro' => null,
    'badge' => null,
    'breadcrumbs' => [],
    'wide' => false,
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
    'title',
    'intro' => null,
    'badge' => null,
    'breadcrumbs' => [],
    'wide' => false,
    'compact' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'public-page-hero public-section public-section--muted public-dot-pattern',
    'public-page-hero--compact' => $compact,
    'public-section--compact' => $compact,
]); ?>" data-reveal-section>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-container', 'public-container--wide' => $wide]); ?>">
        <?php if (isset($component)) { $__componentOriginala858caa4f5dc8b9750c3054e484f0184 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala858caa4f5dc8b9750c3054e484f0184 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.breadcrumbs','data' => ['items' => $breadcrumbs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($breadcrumbs)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala858caa4f5dc8b9750c3054e484f0184)): ?>
<?php $attributes = $__attributesOriginala858caa4f5dc8b9750c3054e484f0184; ?>
<?php unset($__attributesOriginala858caa4f5dc8b9750c3054e484f0184); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala858caa4f5dc8b9750c3054e484f0184)): ?>
<?php $component = $__componentOriginala858caa4f5dc8b9750c3054e484f0184; ?>
<?php unset($__componentOriginala858caa4f5dc8b9750c3054e484f0184); ?>
<?php endif; ?>

        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'mx-auto max-w-3xl text-center',
            'pt-6' => ! $compact,
            'pt-3 sm:pt-4' => $compact,
        ]); ?>" data-animate="fade-up">
            <?php if($badge): ?>
                <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => 'orange','class' => ''.e($compact ? 'mb-3 sm:mb-4' : 'mb-5').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'orange','class' => ''.e($compact ? 'mb-3 sm:mb-4' : 'mb-5').'']); ?><?php echo e($badge); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $attributes = $__attributesOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__attributesOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $component = $__componentOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__componentOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
            <?php endif; ?>

            <h1 class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'public-heading text-display-sm sm:text-display-md',
                'text-2xl sm:text-display-sm' => $compact,
            ]); ?>"><?php echo e($title); ?></h1>

            <?php if($intro): ?>
                <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'public-lead mt-4',
                    'public-page-hero__intro mt-2 max-w-xl text-base leading-snug sm:mt-3 sm:text-body-lg sm:leading-relaxed' => $compact,
                ]); ?>"><?php echo e($intro); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php echo e($slot); ?>

<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\page-hero.blade.php ENDPATH**/ ?>