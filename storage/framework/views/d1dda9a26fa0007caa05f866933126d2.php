<?php
    $promise = config('facility.quality_promise');
    $qualitySlots = [
        'quality.artwork',
        'quality.prepress',
        'quality.color',
        'quality.finishing',
        'quality.packaging',
        'quality.delivery',
    ];
?>

<div class="public-quality-promise public-section public-section--muted public-dot-pattern" data-testid="homepage-quality" data-quality-promise>
    <div class="public-container">
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => 'magenta','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'magenta','class' => 'mb-5']); ?>Quality Promise <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $attributes = $__attributesOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__attributesOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $component = $__componentOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__componentOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
            <h3 class="public-facility__subtitle">The Jana Prints Quality Promise</h3>
            <p class="mt-4 text-base leading-relaxed text-brand-text-secondary sm:text-lg">
                A vertical assurance system — every stage verified before your project moves forward.
            </p>
        </div>

        <div class="public-quality-promise__banner mt-10" data-animate="fade-up">
            <div class="public-quality-promise__seal" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="24" cy="24" r="20"/>
                    <path d="M16 24l5 5 11-12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="public-quality-promise__banner-text"><?php echo e($promise['banner']); ?></p>
        </div>

        <div class="public-quality-spine mt-14" data-quality-spine>
            <div class="public-quality-spine__line" aria-hidden="true" data-quality-spine-line></div>

            <?php $__currentLoopData = $promise['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article
                    class="public-quality-spine__step <?php echo e($index % 2 === 0 ? 'public-quality-spine__step--left' : 'public-quality-spine__step--right'); ?>"
                    data-animate="fade-up"
                    data-animate-delay="<?php echo e(min($index + 1, 5)); ?>"
                    data-quality-step="<?php echo e($index); ?>"
                >
                    <div class="public-quality-spine__node">
                        <span class="public-quality-spine__number"><?php echo e($step['number']); ?></span>
                    </div>

                    <div class="public-quality-spine__card">
                        <div class="public-quality-spine__thumb">
                            <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['slotKey' => $qualitySlots[$index] ?? null,'src' => $step['image'],'alt' => $step['alt'],'fallbackKey' => 'quality','class' => 'h-full w-full object-cover','width' => '120','height' => '120']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['slot-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($qualitySlots[$index] ?? null),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step['alt']),'fallback-key' => 'quality','class' => 'h-full w-full object-cover','width' => '120','height' => '120']); ?>
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
                        <div class="public-quality-spine__content">
                            <h4 class="public-quality-spine__title"><?php echo e($step['title']); ?></h4>
                            <p class="public-quality-spine__value"><?php echo e($step['value']); ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\quality-promise.blade.php ENDPATH**/ ?>