
<section class="public-mobile-stats lg:hidden" aria-label="Trust statistics">
    <div class="public-container">
        <div class="public-mobile-stats__grid">
            <?php $__currentLoopData = config('storefront_stats.mobile'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="public-mobile-stats__item">
                    <p class="public-mobile-stats__value">
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
                    <p class="public-mobile-stats__label"><?php echo e($stat['label']); ?></p>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\mobile-stats-strip.blade.php ENDPATH**/ ?>