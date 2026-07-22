<?php
    $pipeline = config('facility.pipeline');
    $trustPoints = config('facility.trust_points');
    $gallery = config('facility.gallery');
?>

<section id="facility" class="public-facility" data-reveal-section aria-label="Production facility">
    
    <div class="public-facility__header public-section bg-brand-navy relative overflow-hidden">
        <div class="absolute inset-0 opacity-25" data-parallax="0.15">
            <div class="absolute left-0 top-0 h-64 w-64 rounded-full bg-brand-cyan blur-[100px]"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-brand-orange blur-[120px]"></div>
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
<?php $component->withAttributes(['variant' => 'light','class' => 'mb-5']); ?>Behind The Scenes <?php echo $__env->renderComponent(); ?>
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
                    Inside Jana Prints
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-white/70">
                    Take a look behind the scenes and discover how every project moves from
                    artwork approval to professional production and delivery.
                </p>
            </div>
        </div>
    </div>

    
    <div class="public-section bg-white">
        <div class="public-container">
            <div class="public-facility-pipeline" data-facility-pipeline>
                <?php $__currentLoopData = $pipeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal3bbd620aae5b216e3cf502948d1e4263 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3bbd620aae5b216e3cf502948d1e4263 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.facility-pipeline-stage','data' => ['stage' => $stage,'last' => $index === count($pipeline) - 1]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.facility-pipeline-stage'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stage),'last' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($index === count($pipeline) - 1)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3bbd620aae5b216e3cf502948d1e4263)): ?>
<?php $attributes = $__attributesOriginal3bbd620aae5b216e3cf502948d1e4263; ?>
<?php unset($__attributesOriginal3bbd620aae5b216e3cf502948d1e4263); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3bbd620aae5b216e3cf502948d1e4263)): ?>
<?php $component = $__componentOriginal3bbd620aae5b216e3cf502948d1e4263; ?>
<?php unset($__componentOriginal3bbd620aae5b216e3cf502948d1e4263); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginalb04adab4eef6f648d32cbf826bf038f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb04adab4eef6f648d32cbf826bf038f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.production-showcase','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.production-showcase'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb04adab4eef6f648d32cbf826bf038f1)): ?>
<?php $attributes = $__attributesOriginalb04adab4eef6f648d32cbf826bf038f1; ?>
<?php unset($__attributesOriginalb04adab4eef6f648d32cbf826bf038f1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb04adab4eef6f648d32cbf826bf038f1)): ?>
<?php $component = $__componentOriginalb04adab4eef6f648d32cbf826bf038f1; ?>
<?php unset($__componentOriginalb04adab4eef6f648d32cbf826bf038f1); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal2bc7c246e0b14e2298b37f1ddc5128f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2bc7c246e0b14e2298b37f1ddc5128f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.team-showcase','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.team-showcase'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2bc7c246e0b14e2298b37f1ddc5128f9)): ?>
<?php $attributes = $__attributesOriginal2bc7c246e0b14e2298b37f1ddc5128f9; ?>
<?php unset($__attributesOriginal2bc7c246e0b14e2298b37f1ddc5128f9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2bc7c246e0b14e2298b37f1ddc5128f9)): ?>
<?php $component = $__componentOriginal2bc7c246e0b14e2298b37f1ddc5128f9; ?>
<?php unset($__componentOriginal2bc7c246e0b14e2298b37f1ddc5128f9); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal58cdc68b960e00c9361ad873bdaf6c31 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58cdc68b960e00c9361ad873bdaf6c31 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.quality-promise','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.quality-promise'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal58cdc68b960e00c9361ad873bdaf6c31)): ?>
<?php $attributes = $__attributesOriginal58cdc68b960e00c9361ad873bdaf6c31; ?>
<?php unset($__attributesOriginal58cdc68b960e00c9361ad873bdaf6c31); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal58cdc68b960e00c9361ad873bdaf6c31)): ?>
<?php $component = $__componentOriginal58cdc68b960e00c9361ad873bdaf6c31; ?>
<?php unset($__componentOriginal58cdc68b960e00c9361ad873bdaf6c31); ?>
<?php endif; ?>

    
    <div class="public-facility-trust public-section--compact bg-brand-navy">
        <div class="public-container text-center" data-animate="fade-up">
            <h3 class="font-display text-xl font-bold text-white sm:text-2xl">
                Professional Processes Create Better Results
            </h3>
            <div class="public-facility-trust__grid mt-8">
                <?php $__currentLoopData = $trustPoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="public-facility-trust__item">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?php echo e($point); ?>

                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="public-section bg-brand-off-white">
        <div class="public-container">
            <div class="text-center" data-animate="fade-up">
                <h3 class="public-facility__subtitle">Live Production Gallery</h3>
                <p class="mt-3 text-brand-text-secondary">A glimpse of recent print, branding and packaging work from our production floor.</p>
            </div>
            <div class="public-facility-gallery mt-12" data-reveal-stagger>
                <?php $__currentLoopData = $gallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $layoutClass = match ($item['layout'] ?? 'normal') {
                            'tall' => 'public-facility-gallery__item--tall',
                            'wide' => 'public-facility-gallery__item--wide',
                            'hero' => 'public-facility-gallery__item--hero',
                            default => '',
                        };
                    ?>
                    <figure class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-facility-gallery__item', $layoutClass]); ?>" data-animate="fade-up">
                        <div class="public-image-reveal h-full w-full" data-image-reveal>
                            <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $item['image'],'alt' => $item['alt'],'fallback' => 'print_press','width' => '800','height' => '600','class' => 'h-full w-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['alt']),'fallback' => 'print_press','width' => '800','height' => '600','class' => 'h-full w-full object-cover']); ?>
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
                        <figcaption class="public-facility-gallery__caption"><?php echo e($item['alt']); ?></figcaption>
                    </figure>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="mt-10 text-center" data-animate="fade-up">
                <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['href' => ''.e(route('storefront.gallery')).'','variant' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('storefront.gallery')).'','variant' => 'secondary']); ?>
                    View Full Gallery
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
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\facility-section.blade.php ENDPATH**/ ?>