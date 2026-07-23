<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'badge' => null,
    'title',
    'description' => null,
    'light' => false,
    'align' => 'center',
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
    'badge' => null,
    'title',
    'description' => null,
    'light' => false,
    'align' => 'center',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $alignClass = match ($align) {
        'left' => 'text-left',
        default => 'text-center mx-auto',
    };
?>

<div <?php echo e($attributes->merge(['class' => "max-w-2xl mb-16 $alignClass"])); ?> data-animate="fade-up">
    <?php if($badge): ?>
        <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => $light ? 'light' : 'magenta','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($light ? 'light' : 'magenta'),'class' => 'mb-5']); ?>
            <?php echo e($badge); ?>

         <?php echo $__env->renderComponent(); ?>
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

    <h2 class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'public-heading text-display-sm sm:text-display-md',
        'public-heading--light' => $light,
    ]); ?>">
        <?php echo e($title); ?>

    </h2>

    <?php if($description): ?>
        <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'mt-4 public-lead',
            'text-white/70' => $light,
        ]); ?>">
            <?php echo e($description); ?>

        </p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/section-heading.blade.php ENDPATH**/ ?>