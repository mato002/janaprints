<?php
    $showcase = config('facility.production_showcase');
?>

<div class="public-production-showcase public-section bg-brand-off-white" data-production-showcase>
    <div class="public-container">
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <h3 class="public-facility__subtitle">Built For Professional Production</h3>
            <p class="mt-4 text-base leading-relaxed text-brand-text-secondary sm:text-lg">
                From artwork approval to nationwide delivery — every stage runs through
                a structured production workflow built for commercial print.
            </p>
        </div>

        <div class="public-production-flow mt-10" data-animate="fade-up" aria-hidden="true">
            <div class="public-production-flow__track">
                <span class="public-production-flow__line" data-production-flow-line></span>
            </div>
            <div class="public-production-flow__stages">
                <?php $__currentLoopData = $showcase['flow']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span
                        class="public-production-flow__stage"
                        data-production-flow-stage="<?php echo e($index); ?>"
                    ><?php echo e($stage); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="public-production-split mt-10 lg:mt-14">
            <div class="public-production-split__visual max-lg:hidden" data-animate="fade-up">
                <div class="public-production-split__hero-card">
                    <div class="public-image-reveal h-full w-full" data-image-reveal>
                        <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $showcase['hero_image'],'alt' => $showcase['hero_alt'],'fallback' => 'production_floor','class' => 'h-full w-full object-cover','width' => '1200','height' => '800']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showcase['hero_image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showcase['hero_alt']),'fallback' => 'production_floor','class' => 'h-full w-full object-cover','width' => '1200','height' => '800']); ?>
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
                    <div class="public-production-split__hero-badge">
                        <span>Live Production</span>
                    </div>
                </div>
            </div>

            <div class="public-production-split__steps" data-reveal-stagger>
                <?php $__currentLoopData = $showcase['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article
                        class="public-production-step"
                        data-animate="fade-up"
                        data-animate-delay="<?php echo e(min($index + 1, 5)); ?>"
                        data-production-step="<?php echo e($index); ?>"
                    >
                        <div class="public-production-step__icon" aria-hidden="true">
                            <span><?php echo e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        </div>
                        <div class="public-production-step__body">
                            <h4 class="public-production-step__title"><?php echo e($step['title']); ?></h4>
                            <p class="public-production-step__desc"><?php echo e($step['description']); ?></p>
                        </div>
                        <div class="public-production-step__thumb">
                            <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $step['image'],'alt' => $step['alt'],'fallback' => 'print_press','class' => 'h-full w-full object-cover','width' => '120','height' => '80']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step['alt']),'fallback' => 'print_press','class' => 'h-full w-full object-cover','width' => '120','height' => '80']); ?>
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
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\production-showcase.blade.php ENDPATH**/ ?>