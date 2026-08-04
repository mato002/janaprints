<?php
    $visualJourney = config('journey.visual_journey');
    $steps = config('journey.steps');
    $trustPanel = config('journey.trust_panel');
    $assurance = config('journey.assurance');
?>

<section id="workflow" class="public-journey public-section public-section--muted public-dot-pattern" data-reveal-section aria-label="Production journey">
    <div class="public-container">

        
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => 'cyan','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'cyan','class' => 'mb-5']); ?>How It Works <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $attributes = $__attributesOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__attributesOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $component = $__componentOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__componentOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
            <h2 class="public-heading text-display-sm sm:text-display-md">
                From Idea To Delivery
            </h2>
            <p class="public-lead mt-4">
                A clear, structured workflow — from quote request and artwork review
                through approval, production, finishing and delivery.
            </p>
        </div>

        
        <div class="public-journey-visuals mt-10 lg:mt-14" data-animate="fade-up" aria-hidden="true">
            <?php $__currentLoopData = $visualJourney; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($index > 0): ?>
                    <span class="public-journey-visuals__arrow">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                <?php endif; ?>
                <div class="public-journey-visuals__stage">
                    <div class="public-journey-visuals__thumb">
                        <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $stage['image'],'alt' => $stage['alt'],'fallback' => 'artwork','class' => 'h-full w-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stage['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stage['alt']),'fallback' => 'artwork','class' => 'h-full w-full object-cover']); ?>
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
                    <span class="public-journey-visuals__label"><?php echo e($stage['label']); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="public-journey-layout mt-12 lg:mt-16">
            <div class="public-journey-main">
                <div class="public-journey-progress" aria-hidden="true">
                    <div class="public-journey-progress__track">
                        <div class="public-journey-progress__fill" data-journey-progress></div>
                    </div>
                </div>

                <p class="public-h-scroll-hint lg:hidden">Swipe through the process</p>

                <div class="public-journey-timeline public-h-scroll public-h-scroll--journey mt-4 lg:mt-0" data-journey-timeline>
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal93797d5b51066a74a1230d96da8a9edb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal93797d5b51066a74a1230d96da8a9edb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.journey-step','data' => ['step' => $step]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.journey-step'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['step' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal93797d5b51066a74a1230d96da8a9edb)): ?>
<?php $attributes = $__attributesOriginal93797d5b51066a74a1230d96da8a9edb; ?>
<?php unset($__attributesOriginal93797d5b51066a74a1230d96da8a9edb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal93797d5b51066a74a1230d96da8a9edb)): ?>
<?php $component = $__componentOriginal93797d5b51066a74a1230d96da8a9edb; ?>
<?php unset($__componentOriginal93797d5b51066a74a1230d96da8a9edb); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="public-journey-assurance mt-8" data-animate="fade-up">
                    <span class="public-journey-assurance__icon" aria-hidden="true">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="public-journey-assurance__title"><?php echo e($assurance['title']); ?></p>
                        <p class="public-journey-assurance__subtitle"><?php echo e($assurance['subtitle']); ?></p>
                    </div>
                </div>
            </div>

            
            <details class="public-journey-panel" data-journey-panel>
                <summary class="public-journey-panel__summary lg:hidden">
                    <span><?php echo e($trustPanel['title']); ?></span>
                    <svg class="public-journey-panel__chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>

                <div class="public-journey-panel__content" data-animate="fade-left" data-animate-delay="2">
                    <h3 class="public-journey-panel__title hidden lg:block"><?php echo e($trustPanel['title']); ?></h3>

                    <p class="public-journey-panel__headline"><?php echo e($trustPanel['headline']); ?></p>

                    <ul class="public-journey-panel__list">
                        <?php $__currentLoopData = $trustPanel['points']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <?php echo e($point); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>

                    <div class="public-journey-panel__cta">
                        <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['href' => ''.e($quoteFormHref).'','variant' => 'gradient','class' => 'w-full justify-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($quoteFormHref).'','variant' => 'gradient','class' => 'w-full justify-center']); ?>
                            Start Your Project
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab8f5d6997167bf1906093658ed3789d)): ?>
<?php $attributes = $__attributesOriginalab8f5d6997167bf1906093658ed3789d; ?>
<?php unset($__attributesOriginalab8f5d6997167bf1906093658ed3789d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab8f5d6997167bf1906093658ed3789d)): ?>
<?php $component = $__componentOriginalab8f5d6997167bf1906093658ed3789d; ?>
<?php unset($__componentOriginalab8f5d6997167bf1906093658ed3789d); ?>
<?php endif; ?>
                    </div>
                </div>
            </details>
        </div>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\journey-section.blade.php ENDPATH**/ ?>