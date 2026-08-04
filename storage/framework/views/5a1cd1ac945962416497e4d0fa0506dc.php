<?php
    $featured = config('testimonials.featured');
    $videos = config('testimonials.videos');
    $stories = config('testimonials.success_stories');
    $trustCategories = config('testimonials.trust_categories');
    $featuredSlots = [
        'testimonial.featured.sarah',
        'testimonial.featured.james',
        'testimonial.featured.grace',
        'testimonial.featured.david',
    ];
    $videoSlots = [
        'testimonial.video.school',
        'testimonial.video.ngo',
        'testimonial.video.corporate',
        'testimonial.video.retail',
    ];
?>

<section id="testimonials" class="public-testimonials public-section bg-white" data-testid="homepage-testimonials" data-reveal-section aria-label="Testimonials">
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
<?php $component->withAttributes(['variant' => 'magenta','class' => 'mb-5']); ?>Client Success <?php echo $__env->renderComponent(); ?>
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
                What Our Customers Say
            </h2>
            <p class="public-lead mt-4">
                Businesses, schools, NGOs, institutions and corporate organizations trust Jana Prints
                for professional printing, branding and delivery services across Kenya.
            </p>
        </div>

        
        <p class="public-h-scroll-hint mt-10 lg:hidden">Swipe to read more</p>

        <div class="public-testimonials-grid public-h-scroll public-h-scroll--testimonials mt-4 lg:mt-12" data-animate="fade-up">
            <?php $__currentLoopData = $featured; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginalf565c87ffc16705b03ace65b3a528db7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf565c87ffc16705b03ace65b3a528db7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.featured-testimonial','data' => ['testimonial' => $item,'slotKey' => $featuredSlots[$index] ?? null,'dataAnimate' => 'fade-up','dataAnimateDelay' => min($index + 1, 4)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.featured-testimonial'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['testimonial' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item),'slot-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($featuredSlots[$index] ?? null),'data-animate' => 'fade-up','data-animate-delay' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(min($index + 1, 4))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf565c87ffc16705b03ace65b3a528db7)): ?>
<?php $attributes = $__attributesOriginalf565c87ffc16705b03ace65b3a528db7; ?>
<?php unset($__attributesOriginalf565c87ffc16705b03ace65b3a528db7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf565c87ffc16705b03ace65b3a528db7)): ?>
<?php $component = $__componentOriginalf565c87ffc16705b03ace65b3a528db7; ?>
<?php unset($__componentOriginalf565c87ffc16705b03ace65b3a528db7); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="mt-20" data-animate="fade-up">
            <h3 class="mb-8 text-center font-display text-xl font-bold text-brand-navy sm:text-2xl">
                Customer Stories
            </h3>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <?php $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginale1f9ff689410b94917f60104797445f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale1f9ff689410b94917f60104797445f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.video-testimonial','data' => ['video' => $video,'slotKey' => $videoSlots[$index] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.video-testimonial'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['video' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($video),'slot-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($videoSlots[$index] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale1f9ff689410b94917f60104797445f8)): ?>
<?php $attributes = $__attributesOriginale1f9ff689410b94917f60104797445f8; ?>
<?php unset($__attributesOriginale1f9ff689410b94917f60104797445f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale1f9ff689410b94917f60104797445f8)): ?>
<?php $component = $__componentOriginale1f9ff689410b94917f60104797445f8; ?>
<?php unset($__componentOriginale1f9ff689410b94917f60104797445f8); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="mt-20" data-animate="fade-up">
            <h3 class="mb-8 text-center font-display text-xl font-bold text-brand-navy sm:text-2xl">
                Success Stories
            </h3>
            <div class="grid gap-6 lg:grid-cols-3">
                <?php $__currentLoopData = $stories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $story): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal3b08b06638b4e3ad079cbfbb13383460 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b08b06638b4e3ad079cbfbb13383460 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.success-story','data' => ['story' => $story]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.success-story'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['story' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($story)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b08b06638b4e3ad079cbfbb13383460)): ?>
<?php $attributes = $__attributesOriginal3b08b06638b4e3ad079cbfbb13383460; ?>
<?php unset($__attributesOriginal3b08b06638b4e3ad079cbfbb13383460); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b08b06638b4e3ad079cbfbb13383460)): ?>
<?php $component = $__componentOriginal3b08b06638b4e3ad079cbfbb13383460; ?>
<?php unset($__componentOriginal3b08b06638b4e3ad079cbfbb13383460); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="mt-20" data-animate="fade-up">
            <h3 class="mb-8 text-center text-sm font-semibold uppercase tracking-wider text-brand-text-muted">
                Trusted By Organizations Across Kenya
            </h3>
            <div class="public-testimonials-trust">
                <?php $__currentLoopData = $trustCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginale5956e70f20a3a6006140eb70815afa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5956e70f20a3a6006140eb70815afa3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.trust-category','data' => ['category' => $cat['label'],'icon' => $cat['icon']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.trust-category'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cat['label']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cat['icon'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5956e70f20a3a6006140eb70815afa3)): ?>
<?php $attributes = $__attributesOriginale5956e70f20a3a6006140eb70815afa3; ?>
<?php unset($__attributesOriginale5956e70f20a3a6006140eb70815afa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5956e70f20a3a6006140eb70815afa3)): ?>
<?php $component = $__componentOriginale5956e70f20a3a6006140eb70815afa3; ?>
<?php unset($__componentOriginale5956e70f20a3a6006140eb70815afa3); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="mt-16" data-animate="fade">
            <?php if (isset($component)) { $__componentOriginal9c9ea2d40f6f58eccb6751e35364badb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c9ea2d40f6f58eccb6751e35364badb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.review-marquee','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.review-marquee'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c9ea2d40f6f58eccb6751e35364badb)): ?>
<?php $attributes = $__attributesOriginal9c9ea2d40f6f58eccb6751e35364badb; ?>
<?php unset($__attributesOriginal9c9ea2d40f6f58eccb6751e35364badb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c9ea2d40f6f58eccb6751e35364badb)): ?>
<?php $component = $__componentOriginal9c9ea2d40f6f58eccb6751e35364badb; ?>
<?php unset($__componentOriginal9c9ea2d40f6f58eccb6751e35364badb); ?>
<?php endif; ?>
        </div>

        
        <div class="mt-16 hidden text-center lg:block" data-animate="fade-up">
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
                Request Your Quote
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
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\testimonials-section.blade.php ENDPATH**/ ?>