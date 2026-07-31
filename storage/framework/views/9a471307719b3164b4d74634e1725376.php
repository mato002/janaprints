<?php if (isset($component)) { $__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.public','data' => ['seo' => $seo]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.public'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['seo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seo)]); ?>

    
    <section class="public-hero" data-testid="homepage-hero" data-reveal-section aria-label="Hero">
        <div class="public-hero__bg"></div>

        <div class="public-hero__glow-layer opacity-50" data-parallax="0.4">
            <div class="absolute -left-24 top-16 h-96 w-96 rounded-full bg-brand-magenta blur-[120px]"></div>
            <div class="absolute -right-16 bottom-0 h-[28rem] w-[28rem] rounded-full bg-brand-orange blur-[140px]"></div>
            <div class="absolute left-1/3 top-1/2 h-80 w-80 rounded-full bg-brand-purple opacity-40 blur-[100px]"></div>
            <div class="absolute bottom-1/4 right-1/3 h-64 w-64 rounded-full bg-brand-cyan opacity-20 blur-[80px]"></div>
        </div>

        <div class="public-hero__cmyk-grid absolute inset-0"></div>
        <div class="public-hero-pattern absolute inset-0 opacity-60"></div>

        <div class="public-container public-container--hero public-section--hero public-hero__content relative">
            <div class="public-hero__grid">
                
                <div class="public-hero__copy order-1 lg:order-none" data-animate="fade-up">
                    <span class="public-hero-badge">
                        <span class="h-2 w-2 rounded-full bg-brand-cyan animate-pulse"></span>
                        Kenya's Trusted Print &amp; Branding Partner
                    </span>

                    <h1 class="font-display text-4xl font-extrabold leading-[1.06] tracking-tight text-white sm:text-5xl lg:text-[3.5rem] xl:text-display-lg">
                        Jana Prints — Professional Printing Services in Kenya
                    </h1>

                    <p class="mt-5 text-base leading-relaxed text-white/80 sm:text-lg">
                        From business cards and brochures to packaging, large-format printing,
                        corporate branding and nationwide delivery — we help businesses create
                        professional print experiences that leave lasting impressions.
                    </p>

                    <ul class="public-hero-trust public-hero-trust--mobile-compact">
                        <?php $__currentLoopData = ['Fast Turnaround', 'Artwork Approval', 'Nationwide Delivery']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="public-hero-trust__item">
                                <span class="public-hero-trust__icon">
                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <?php echo e($item); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center">
                        <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['href' => ''.e($quoteFormHref).'','variant' => 'gradient','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($quoteFormHref).'','variant' => 'gradient','size' => 'lg']); ?>
                            Request Quote
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
                        <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['href' => '#recent-work','variant' => 'outline-light','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => '#recent-work','variant' => 'outline-light','size' => 'lg']); ?>
                            View Our Work
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

                    <?php if (isset($component)) { $__componentOriginaldb64139a9ea6c07e2d32317ddf83e0ba = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldb64139a9ea6c07e2d32317ddf83e0ba = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.hero-stats','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.hero-stats'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldb64139a9ea6c07e2d32317ddf83e0ba)): ?>
<?php $attributes = $__attributesOriginaldb64139a9ea6c07e2d32317ddf83e0ba; ?>
<?php unset($__attributesOriginaldb64139a9ea6c07e2d32317ddf83e0ba); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldb64139a9ea6c07e2d32317ddf83e0ba)): ?>
<?php $component = $__componentOriginaldb64139a9ea6c07e2d32317ddf83e0ba; ?>
<?php unset($__componentOriginaldb64139a9ea6c07e2d32317ddf83e0ba); ?>
<?php endif; ?>
                </div>

                
                <div class="public-hero__visual order-2 lg:order-none" data-animate="fade-right" data-animate-delay="1">
                    <?php if (isset($component)) { $__componentOriginal0ac267d7a06abcd7c3366b5c278fa1f5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ac267d7a06abcd7c3366b5c278fa1f5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.hero-showcase','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.hero-showcase'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ac267d7a06abcd7c3366b5c278fa1f5)): ?>
<?php $attributes = $__attributesOriginal0ac267d7a06abcd7c3366b5c278fa1f5; ?>
<?php unset($__attributesOriginal0ac267d7a06abcd7c3366b5c278fa1f5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ac267d7a06abcd7c3366b5c278fa1f5)): ?>
<?php $component = $__componentOriginal0ac267d7a06abcd7c3366b5c278fa1f5; ?>
<?php unset($__componentOriginal0ac267d7a06abcd7c3366b5c278fa1f5); ?>
<?php endif; ?>
                </div>
            </div>
        </div>

    </section>

    
    <?php if (isset($component)) { $__componentOriginal72016ce40b9109e8f78daf10c487d55c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72016ce40b9109e8f78daf10c487d55c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.services-section','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.services-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72016ce40b9109e8f78daf10c487d55c)): ?>
<?php $attributes = $__attributesOriginal72016ce40b9109e8f78daf10c487d55c; ?>
<?php unset($__attributesOriginal72016ce40b9109e8f78daf10c487d55c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72016ce40b9109e8f78daf10c487d55c)): ?>
<?php $component = $__componentOriginal72016ce40b9109e8f78daf10c487d55c; ?>
<?php unset($__componentOriginal72016ce40b9109e8f78daf10c487d55c); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal5bd00e1b0dce7dc191140b24d06aa060 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5bd00e1b0dce7dc191140b24d06aa060 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.gallery-preview-section','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.gallery-preview-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5bd00e1b0dce7dc191140b24d06aa060)): ?>
<?php $attributes = $__attributesOriginal5bd00e1b0dce7dc191140b24d06aa060; ?>
<?php unset($__attributesOriginal5bd00e1b0dce7dc191140b24d06aa060); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5bd00e1b0dce7dc191140b24d06aa060)): ?>
<?php $component = $__componentOriginal5bd00e1b0dce7dc191140b24d06aa060; ?>
<?php unset($__componentOriginal5bd00e1b0dce7dc191140b24d06aa060); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginale2994eddacb8dd1d2ce6ec9c92d07997 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale2994eddacb8dd1d2ce6ec9c92d07997 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.workflow-section','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.workflow-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale2994eddacb8dd1d2ce6ec9c92d07997)): ?>
<?php $attributes = $__attributesOriginale2994eddacb8dd1d2ce6ec9c92d07997; ?>
<?php unset($__attributesOriginale2994eddacb8dd1d2ce6ec9c92d07997); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale2994eddacb8dd1d2ce6ec9c92d07997)): ?>
<?php $component = $__componentOriginale2994eddacb8dd1d2ce6ec9c92d07997; ?>
<?php unset($__componentOriginale2994eddacb8dd1d2ce6ec9c92d07997); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal074010dcf37f6a19209d01a6d7bee5e5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074010dcf37f6a19209d01a6d7bee5e5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.inside-jana-section','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.inside-jana-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074010dcf37f6a19209d01a6d7bee5e5)): ?>
<?php $attributes = $__attributesOriginal074010dcf37f6a19209d01a6d7bee5e5; ?>
<?php unset($__attributesOriginal074010dcf37f6a19209d01a6d7bee5e5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074010dcf37f6a19209d01a6d7bee5e5)): ?>
<?php $component = $__componentOriginal074010dcf37f6a19209d01a6d7bee5e5; ?>
<?php unset($__componentOriginal074010dcf37f6a19209d01a6d7bee5e5); ?>
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

    
    <?php if (isset($component)) { $__componentOriginal7a7ffc1a990add0efce59dd68be27125 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7a7ffc1a990add0efce59dd68be27125 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.testimonials-section','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.testimonials-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7a7ffc1a990add0efce59dd68be27125)): ?>
<?php $attributes = $__attributesOriginal7a7ffc1a990add0efce59dd68be27125; ?>
<?php unset($__attributesOriginal7a7ffc1a990add0efce59dd68be27125); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7a7ffc1a990add0efce59dd68be27125)): ?>
<?php $component = $__componentOriginal7a7ffc1a990add0efce59dd68be27125; ?>
<?php unset($__componentOriginal7a7ffc1a990add0efce59dd68be27125); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal191dfa3bc6b4cb0d07835854a454578b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal191dfa3bc6b4cb0d07835854a454578b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.final-cta-section','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.final-cta-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal191dfa3bc6b4cb0d07835854a454578b)): ?>
<?php $attributes = $__attributesOriginal191dfa3bc6b4cb0d07835854a454578b; ?>
<?php unset($__attributesOriginal191dfa3bc6b4cb0d07835854a454578b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal191dfa3bc6b4cb0d07835854a454578b)): ?>
<?php $component = $__componentOriginal191dfa3bc6b4cb0d07835854a454578b; ?>
<?php unset($__componentOriginal191dfa3bc6b4cb0d07835854a454578b); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalc16f49387bac09e20bea30d8b3ec5c15 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc16f49387bac09e20bea30d8b3ec5c15 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.quote-form','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.quote-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc16f49387bac09e20bea30d8b3ec5c15)): ?>
<?php $attributes = $__attributesOriginalc16f49387bac09e20bea30d8b3ec5c15; ?>
<?php unset($__attributesOriginalc16f49387bac09e20bea30d8b3ec5c15); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc16f49387bac09e20bea30d8b3ec5c15)): ?>
<?php $component = $__componentOriginalc16f49387bac09e20bea30d8b3ec5c15; ?>
<?php unset($__componentOriginalc16f49387bac09e20bea30d8b3ec5c15); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal4f148cf80961598c35eb8116be8b24cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f148cf80961598c35eb8116be8b24cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.contact-section','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.contact-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f148cf80961598c35eb8116be8b24cb)): ?>
<?php $attributes = $__attributesOriginal4f148cf80961598c35eb8116be8b24cb; ?>
<?php unset($__attributesOriginal4f148cf80961598c35eb8116be8b24cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f148cf80961598c35eb8116be8b24cb)): ?>
<?php $component = $__componentOriginal4f148cf80961598c35eb8116be8b24cb; ?>
<?php unset($__componentOriginal4f148cf80961598c35eb8116be8b24cb); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal3ed898c95a03e95cbf1f167d61b4c261 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ed898c95a03e95cbf1f167d61b4c261 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.contact-map-section','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.contact-map-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3ed898c95a03e95cbf1f167d61b4c261)): ?>
<?php $attributes = $__attributesOriginal3ed898c95a03e95cbf1f167d61b4c261; ?>
<?php unset($__attributesOriginal3ed898c95a03e95cbf1f167d61b4c261); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3ed898c95a03e95cbf1f167d61b4c261)): ?>
<?php $component = $__componentOriginal3ed898c95a03e95cbf1f167d61b4c261; ?>
<?php unset($__componentOriginal3ed898c95a03e95cbf1f167d61b4c261); ?>
<?php endif; ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd)): ?>
<?php $attributes = $__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd; ?>
<?php unset($__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd)): ?>
<?php $component = $__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd; ?>
<?php unset($__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/welcome.blade.php ENDPATH**/ ?>