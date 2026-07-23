<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['stage', 'last' => false]));

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

foreach (array_filter((['stage', 'last' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $workflowSlots = [
        'Artwork Review' => 'workflow.artwork-review',
        'Pre-Press' => 'workflow.pre-press',
        'Printing' => 'workflow.printing',
        'Finishing' => 'workflow.finishing',
        'Quality Control' => 'workflow.quality-control',
        'Packaging' => 'workflow.packaging',
        'Delivery' => 'workflow.delivery',
    ];
    $workflowSlot = $workflowSlots[$stage['title'] ?? ''] ?? null;
    $icons = [
        'design' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
        'prepress' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'print' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>',
        'finish' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/>',
        'qc' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'package' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'delivery' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>',
    ];
?>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-facility-pipeline__stage', 'public-facility-pipeline__stage--last' => $last]); ?>" data-animate="fade-up">
    <div class="public-facility-pipeline__card">
        <div class="public-facility-pipeline__thumb">
            <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['slotKey' => $workflowSlot,'src' => $stage['image'],'alt' => $stage['alt'],'fallbackKey' => 'artwork','class' => 'h-full w-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['slot-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($workflowSlot),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stage['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stage['alt']),'fallback-key' => 'artwork','class' => 'h-full w-full object-cover']); ?>
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
            <span class="public-facility-pipeline__icon">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <?php echo $icons[$stage['icon']] ?? $icons['print']; ?>

                </svg>
            </span>
        </div>
        <h4 class="public-facility-pipeline__title"><?php echo e($stage['title']); ?></h4>
        <p class="public-facility-pipeline__desc"><?php echo e($stage['description']); ?></p>
    </div>
    <?php if (! ($last)): ?>
        <span class="public-facility-pipeline__arrow" aria-hidden="true">
            <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </span>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/facility-pipeline-stage.blade.php ENDPATH**/ ?>