<?php
    $team = config('facility.team');
    $teamSlots = [
        'team.management',
        'team.design',
        'team.production',
        'team.quality',
        'team.support',
        'team.dispatch',
    ];
?>

<section id="team" class="public-team-showcase public-section bg-white relative overflow-hidden" data-testid="homepage-team" data-reveal-section aria-label="The people behind every project">
    <div class="public-team-showcase__glow" aria-hidden="true"></div>
    <div class="public-team-showcase__accent-line" aria-hidden="true" data-team-accent-line></div>

    <div class="public-container relative">
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
<?php $component->withAttributes(['variant' => 'navy','class' => 'mb-5']); ?>Our Teams <?php echo $__env->renderComponent(); ?>
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
                The People Behind Every Project
            </h2>
            <p class="public-lead mt-4">
                Every job is supported by people responsible for planning, design review,
                production quality, customer communication and dispatch.
            </p>
        </div>

        <p class="public-h-scroll-hint mt-10 lg:hidden">Swipe to view more</p>

        <div class="public-team-showcase__grid public-h-scroll public-h-scroll--team mt-4 lg:mt-14" data-reveal-stagger>
            <?php $__currentLoopData = $team; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article
                    class="public-team-card"
                    data-animate="fade-up"
                    data-animate-delay="<?php echo e(min($index + 1, 5)); ?>"
                >
                    <div class="public-team-card__photo">
                        <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['slotKey' => $teamSlots[$index] ?? null,'src' => $member['photo'],'alt' => $member['alt'],'fallbackKey' => 'team','class' => 'h-full w-full object-cover','width' => '600','height' => '450']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['slot-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($teamSlots[$index] ?? null),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member['photo']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member['alt']),'fallback-key' => 'team','class' => 'h-full w-full object-cover','width' => '600','height' => '450']); ?>
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
                    <div class="public-team-card__body">
                        <h3 class="public-team-card__name"><?php echo e($member['name']); ?></h3>
                        <p class="public-team-card__role"><?php echo e($member['role']); ?></p>
                        <p class="public-team-card__bio"><?php echo e($member['bio']); ?></p>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/team-showcase.blade.php ENDPATH**/ ?>