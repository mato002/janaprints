<?php
    $stats = config('storefront_stats.trust_strip');
?>

<div class="public-hero-proof" data-animate="fade-up" data-animate-delay="2" aria-label="Trust statistics">
    <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="public-hero-proof__item">
            <p class="public-hero-proof__value">
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
            <p class="public-hero-proof__label"><?php echo e($stat['label']); ?></p>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\hero-stats.blade.php ENDPATH**/ ?>