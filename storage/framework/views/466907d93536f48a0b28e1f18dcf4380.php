<?php
    $blocks = config('inside_jana.blocks');
?>

<section id="inside-jana" class="public-inside-jana" data-testid="homepage-inside-jana" data-reveal-section aria-label="Inside Jana Prints">
    <div class="public-inside-jana__header public-section bg-brand-navy relative overflow-hidden">
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
                    A behind-the-scenes look at how artwork, production, finishing and dispatch
                    come together before every job reaches the customer.
                </p>
            </div>
        </div>
    </div>

    <div class="public-inside-jana__blocks">
        <?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginal49c33e1e51dfdc214e1a26850032b24e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49c33e1e51dfdc214e1a26850032b24e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.inside-jana-block','data' => ['block' => $block,'reversed' => $index % 2 === 1]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.inside-jana-block'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['block' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($block),'reversed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($index % 2 === 1)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49c33e1e51dfdc214e1a26850032b24e)): ?>
<?php $attributes = $__attributesOriginal49c33e1e51dfdc214e1a26850032b24e; ?>
<?php unset($__attributesOriginal49c33e1e51dfdc214e1a26850032b24e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49c33e1e51dfdc214e1a26850032b24e)): ?>
<?php $component = $__componentOriginal49c33e1e51dfdc214e1a26850032b24e; ?>
<?php unset($__componentOriginal49c33e1e51dfdc214e1a26850032b24e); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\inside-jana-section.blade.php ENDPATH**/ ?>