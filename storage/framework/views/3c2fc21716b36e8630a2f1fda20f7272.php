<?php
    $features = config('why-us.features');
    $stats = config('storefront_stats.why_us');
    $comparison = config('why-us.comparison');
?>

<section id="about" class="public-why" data-reveal-section aria-label="Why choose Jana Prints">
    
    <div class="public-why__header public-section public-section--dark relative overflow-hidden">
        <div class="absolute inset-0 opacity-30" data-parallax="0.2">
            <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-brand-magenta blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 h-80 w-80 rounded-full bg-brand-orange blur-[100px]"></div>
        </div>

        <div class="public-container relative">
            <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
                <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => 'light','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'light','class' => 'mb-5']); ?>Why Jana Prints <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $attributes = $__attributesOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__attributesOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $component = $__componentOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__componentOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
                <h2 class="public-heading public-heading--light text-display-sm sm:text-display-md">
                    Why Businesses Choose Jana Prints
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-white/70">
                    More than printing. We combine design, production, quality control,
                    project management and delivery to ensure every project is completed
                    professionally and on time.
                </p>
            </div>
        </div>
    </div>

    
    <div class="public-why__features">
        <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginal0dc03f251654ec33aec3465f4e6e9f6b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0dc03f251654ec33aec3465f4e6e9f6b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.why-feature-block','data' => ['feature' => $feature,'reversed' => $index % 2 === 1]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.why-feature-block'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['feature' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($feature),'reversed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($index % 2 === 1)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0dc03f251654ec33aec3465f4e6e9f6b)): ?>
<?php $attributes = $__attributesOriginal0dc03f251654ec33aec3465f4e6e9f6b; ?>
<?php unset($__attributesOriginal0dc03f251654ec33aec3465f4e6e9f6b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0dc03f251654ec33aec3465f4e6e9f6b)): ?>
<?php $component = $__componentOriginal0dc03f251654ec33aec3465f4e6e9f6b; ?>
<?php unset($__componentOriginal0dc03f251654ec33aec3465f4e6e9f6b); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="public-why__stats-wrap public-section--compact bg-brand-off-white max-lg:hidden">
        <div class="public-container">
            <div class="public-why-stats" data-animate="fade-up">
                <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="public-why-stats__item">
                        <p class="public-why-stats__value">
                            <?php if (isset($component)) { $__componentOriginal90ae1e1cad06f688995159972e707a80 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal90ae1e1cad06f688995159972e707a80 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.counter','data' => ['value' => $stat['value'],'suffix' => $stat['suffix']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.counter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stat['value']),'suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stat['suffix'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal90ae1e1cad06f688995159972e707a80)): ?>
<?php $attributes = $__attributesOriginal90ae1e1cad06f688995159972e707a80; ?>
<?php unset($__attributesOriginal90ae1e1cad06f688995159972e707a80); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal90ae1e1cad06f688995159972e707a80)): ?>
<?php $component = $__componentOriginal90ae1e1cad06f688995159972e707a80; ?>
<?php unset($__componentOriginal90ae1e1cad06f688995159972e707a80); ?>
<?php endif; ?>
                        </p>
                        <p class="public-why-stats__label"><?php echo e($stat['label']); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="public-why__compare public-section--compact bg-brand-navy">
        <div class="public-container">
            <h3 class="public-why-compare__title text-center" data-animate="fade-up">
                <?php echo e($comparison['title']); ?>

            </h3>

            <div class="public-why-compare mt-10" data-animate="fade-up">
                <div class="public-why-compare__col public-why-compare__col--traditional">
                    <h4 class="public-why-compare__heading"><?php echo e($comparison['traditional']['label']); ?></h4>
                    <ul class="public-why-compare__list">
                        <?php $__currentLoopData = $comparison['traditional']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <?php echo e($item); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <div class="public-why-compare__vs" aria-hidden="true">vs</div>

                <div class="public-why-compare__col public-why-compare__col--jana">
                    <h4 class="public-why-compare__heading"><?php echo e($comparison['jana']['label']); ?></h4>
                    <ul class="public-why-compare__list">
                        <?php $__currentLoopData = $comparison['jana']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <?php echo e($item); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\why-section.blade.php ENDPATH**/ ?>