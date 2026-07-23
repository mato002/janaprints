
<section id="trust" class="public-trust public-section bg-white" data-reveal-section aria-label="Trust and social proof">
    <div class="public-container">

        
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => 'navy','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'navy','class' => 'mb-5']); ?>Established &amp; Trusted <?php echo $__env->renderComponent(); ?>
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
                Trusted By Businesses Across Kenya
            </h2>
            <p class="public-lead mt-4">
                From startups and schools to NGOs, corporates, manufacturers, and government institutions,
                Jana Prints delivers professional print solutions at scale.
            </p>
        </div>

        
        <div class="public-trust-visuals mt-14" data-animate="fade-up" data-animate-delay="1">
            <?php $__currentLoopData = [
                ['src' => 'brochure', 'alt' => 'Brochure and catalogue printing'],
                ['src' => 'stationery', 'alt' => 'Corporate stationery printing'],
                ['src' => 'packaging', 'alt' => 'Product packaging printing'],
                ['src' => 'merchandise', 'alt' => 'Promotional merchandise printing'],
                ['src' => 'cards', 'alt' => 'Business card printing'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visual): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="public-trust-visuals__item">
                    <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $visual['src'],'alt' => $visual['alt'],'class' => 'h-full w-full object-cover rounded-brand-md','width' => '200','height' => '200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($visual['src']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($visual['alt']),'class' => 'h-full w-full object-cover rounded-brand-md','width' => '200','height' => '200']); ?>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="public-trust-stats mt-14 max-lg:hidden">
            <?php $__currentLoopData = config('storefront_stats.trust'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article
                    class="public-trust-stat-card"
                    data-animate="fade-up"
                    data-animate-delay="<?php echo e(($index % 3) + 1); ?>"
                >
                    <p class="public-trust-stat-card__value">
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
                    <p class="public-trust-stat-card__label"><?php echo e($stat['label']); ?></p>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="mt-16" data-animate="fade-up">
            <?php if (isset($component)) { $__componentOriginal05788db6584a50a24bafa7b634dde0bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal05788db6584a50a24bafa7b634dde0bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.testimonial-carousel','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.testimonial-carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal05788db6584a50a24bafa7b634dde0bc)): ?>
<?php $attributes = $__attributesOriginal05788db6584a50a24bafa7b634dde0bc; ?>
<?php unset($__attributesOriginal05788db6584a50a24bafa7b634dde0bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal05788db6584a50a24bafa7b634dde0bc)): ?>
<?php $component = $__componentOriginal05788db6584a50a24bafa7b634dde0bc; ?>
<?php unset($__componentOriginal05788db6584a50a24bafa7b634dde0bc); ?>
<?php endif; ?>
        </div>

        
        <div class="mt-16" data-animate="fade-up">
            <h3 class="mb-8 text-center text-sm font-semibold uppercase tracking-wider text-brand-text-muted">
                Trusted By
            </h3>
            <?php if (isset($component)) { $__componentOriginalf26c75b18f2b8daba5aba747ec4ed2c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf26c75b18f2b8daba5aba747ec4ed2c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.client-logos','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.client-logos'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf26c75b18f2b8daba5aba747ec4ed2c3)): ?>
<?php $attributes = $__attributesOriginalf26c75b18f2b8daba5aba747ec4ed2c3; ?>
<?php unset($__attributesOriginalf26c75b18f2b8daba5aba747ec4ed2c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf26c75b18f2b8daba5aba747ec4ed2c3)): ?>
<?php $component = $__componentOriginalf26c75b18f2b8daba5aba747ec4ed2c3; ?>
<?php unset($__componentOriginalf26c75b18f2b8daba5aba747ec4ed2c3); ?>
<?php endif; ?>
        </div>

        
        <div class="public-trust-chips mt-16" data-animate="fade-up">
            <?php $__currentLoopData = [
                'Quality Controlled Production',
                'Dedicated Account Managers',
                'Nationwide Delivery',
                'Secure Artwork Approval',
                'Professional Design Support',
                'Corporate Billing Available',
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="public-trust-chip">
                    <svg class="public-trust-chip__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <?php echo e($chip); ?>

                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\trust-section.blade.php ENDPATH**/ ?>