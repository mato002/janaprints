
<?php
    $galleryService = app(\App\Services\Storefront\PublicGalleryService::class);
    $projects = ($fullPage ?? false)
        ? $galleryService->allItems()
        : $galleryService->homepageItems(12);
    $filters = $galleryService->categoriesWithItems();
    $showGalleryCta = $showGalleryCta ?? true;
    $isCompact = $compact ?? false;
?>

<section
    id="portfolio"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'public-portfolio public-section bg-white',
        'public-portfolio--compact public-section--compact' => $isCompact,
    ]); ?>"
    data-reveal-section
    aria-label="Portfolio"
>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-container', 'public-container--wide' => true]); ?>">

        
        <div class="public-portfolio-header" data-animate="fade-up">
            <div class="public-portfolio-header__content">
                <?php if (! ($isCompact)): ?>
                    <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => 'magenta','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'magenta','class' => 'mb-4']); ?>Portfolio <?php echo $__env->renderComponent(); ?>
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
                    'text-xl sm:text-2xl lg:text-display-sm' => $isCompact,
                ]); ?>">
                    <?php echo e($heading ?? 'Featured Work'); ?>

                </h2>
                <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'public-lead mt-3 max-w-2xl',
                    'mt-2 max-w-lg text-sm leading-snug sm:text-base sm:leading-relaxed' => $isCompact,
                ]); ?>">
                    <?php echo e($intro ?? 'A quick look at recent print, branding and packaging work.'); ?>

                </p>

                <?php if($showGalleryCta && ! ($fullPage ?? false)): ?>
                    <div class="public-portfolio-header__cta-mobile mt-5 lg:hidden">
                        <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['href' => ''.e(route('storefront.gallery')).'','variant' => 'secondary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('storefront.gallery')).'','variant' => 'secondary','size' => 'sm']); ?>
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
                <?php endif; ?>
            </div>

            <?php if($showGalleryCta && ! ($fullPage ?? false)): ?>
                <div class="public-portfolio-header__cta-desktop hidden shrink-0 lg:block" data-animate="fade-up" data-animate-delay="1">
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
            <?php endif; ?>
        </div>

        
        <?php if(count($filters) > 1): ?>
            <div
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'public-portfolio-filters',
                    'mt-8 lg:mt-10' => ! $isCompact,
                    'mt-4 lg:mt-6' => $isCompact,
                ]); ?>"
                data-portfolio-filters
                role="tablist"
                aria-label="Filter portfolio"
            >
                <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        type="button"
                        role="tab"
                        class="public-portfolio-filters__btn <?php echo e($filter['slug'] === 'all' ? 'is-active' : ''); ?>"
                        data-filter="<?php echo e($filter['slug']); ?>"
                        aria-selected="<?php echo e($filter['slug'] === 'all' ? 'true' : 'false'); ?>"
                    >
                        <?php echo e($filter['label']); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'public-masonry-gallery',
            'mt-6 lg:mt-8' => ! $isCompact,
            'mt-4 lg:mt-6' => $isCompact,
        ]); ?>" data-portfolio-grid>
            <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginal83da9838db23561fa26d0489626c8a19 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal83da9838db23561fa26d0489626c8a19 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.portfolio-card','data' => ['project' => $project]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.portfolio-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['project' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal83da9838db23561fa26d0489626c8a19)): ?>
<?php $attributes = $__attributesOriginal83da9838db23561fa26d0489626c8a19; ?>
<?php unset($__attributesOriginal83da9838db23561fa26d0489626c8a19); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal83da9838db23561fa26d0489626c8a19)): ?>
<?php $component = $__componentOriginal83da9838db23561fa26d0489626c8a19; ?>
<?php unset($__componentOriginal83da9838db23561fa26d0489626c8a19); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>

    <?php if (isset($component)) { $__componentOriginald80c413d0f65c7cb1335ede16fe8e543 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald80c413d0f65c7cb1335ede16fe8e543 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.portfolio-modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.portfolio-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald80c413d0f65c7cb1335ede16fe8e543)): ?>
<?php $attributes = $__attributesOriginald80c413d0f65c7cb1335ede16fe8e543; ?>
<?php unset($__attributesOriginald80c413d0f65c7cb1335ede16fe8e543); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald80c413d0f65c7cb1335ede16fe8e543)): ?>
<?php $component = $__componentOriginald80c413d0f65c7cb1335ede16fe8e543; ?>
<?php unset($__componentOriginald80c413d0f65c7cb1335ede16fe8e543); ?>
<?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\portfolio-section.blade.php ENDPATH**/ ?>