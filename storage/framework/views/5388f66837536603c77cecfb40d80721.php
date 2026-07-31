<?php
    $capabilities = config('capabilities.capabilities');
    $trustPoints = config('capabilities.trust_points');
?>

<section id="services" class="public-services" data-testid="homepage-capabilities" data-reveal-section aria-label="Services and capabilities">
    
    <div class="public-services__header public-section public-section--muted public-dot-pattern">
        <div class="public-container">
            <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
                <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => 'orange','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'orange','class' => 'mb-5']); ?>Our Capabilities <?php echo $__env->renderComponent(); ?>
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
                    Everything You Need To Print, Brand &amp; Grow
                </h2>
                <p class="public-lead mt-4">
                    From business cards and brochures to corporate branding, packaging, signage,
                    promotional merchandise and large-format printing, Jana Prints helps businesses
                    create lasting impressions.
                </p>
            </div>
        </div>
    </div>

    
    <div class="public-services__blocks">
        <?php $__currentLoopData = $capabilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $capability): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginalf795d74e26fe69663012ebf6fff1d7c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf795d74e26fe69663012ebf6fff1d7c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.capability-block','data' => ['capability' => $capability,'reversed' => $index % 2 === 1,'trustPoints' => $trustPoints]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.capability-block'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['capability' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($capability),'reversed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($index % 2 === 1),'trust-points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trustPoints)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf795d74e26fe69663012ebf6fff1d7c1)): ?>
<?php $attributes = $__attributesOriginalf795d74e26fe69663012ebf6fff1d7c1; ?>
<?php unset($__attributesOriginalf795d74e26fe69663012ebf6fff1d7c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf795d74e26fe69663012ebf6fff1d7c1)): ?>
<?php $component = $__componentOriginalf795d74e26fe69663012ebf6fff1d7c1; ?>
<?php unset($__componentOriginalf795d74e26fe69663012ebf6fff1d7c1); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/services-section.blade.php ENDPATH**/ ?>