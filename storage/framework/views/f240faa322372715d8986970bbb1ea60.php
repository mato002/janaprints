<?php
    $pipeline = config('facility.pipeline');
    $techniques = config('facility.capabilities');
?>

<section id="workflow" class="public-workflow public-section public-section--muted public-dot-pattern" data-testid="homepage-process" data-reveal-section aria-label="How Jana Prints delivers">
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
<?php $component->withAttributes(['variant' => 'navy','class' => 'mb-5']); ?>Production Workflow <?php echo $__env->renderComponent(); ?>
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
                How Jana Prints Delivers
            </h2>
            <p class="public-lead mt-4">
                A structured production workflow — from artwork review and pre-press through
                printing, finishing, quality control, packaging and delivery.
            </p>
        </div>

        <p class="public-h-scroll-hint mt-10 lg:hidden">Swipe through the workflow</p>

        <div class="public-facility-pipeline public-h-scroll public-h-scroll--workflow mt-4 lg:mt-14" data-facility-pipeline>
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

        <div class="public-workflow-techniques mt-12 lg:mt-16" data-animate="fade-up">
            <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-brand-text-muted">
                In-House Production Techniques
            </h3>
            <ul class="public-workflow-techniques__list">
                <?php $__currentLoopData = $techniques; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $technique): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($technique['title']); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\workflow-section.blade.php ENDPATH**/ ?>