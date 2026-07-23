<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <?php if (isset($component)) { $__componentOriginal2b8b5fdb5cc0e9bec7ef17307ccdef1e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b8b5fdb5cc0e9bec7ef17307ccdef1e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.seo-meta','data' => ['seo' => $seo ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.seo-meta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['seo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seo ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b8b5fdb5cc0e9bec7ef17307ccdef1e)): ?>
<?php $attributes = $__attributesOriginal2b8b5fdb5cc0e9bec7ef17307ccdef1e; ?>
<?php unset($__attributesOriginal2b8b5fdb5cc0e9bec7ef17307ccdef1e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b8b5fdb5cc0e9bec7ef17307ccdef1e)): ?>
<?php $component = $__componentOriginal2b8b5fdb5cc0e9bec7ef17307ccdef1e; ?>
<?php unset($__componentOriginal2b8b5fdb5cc0e9bec7ef17307ccdef1e); ?>
<?php endif; ?>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">

    <noscript>
        <style>
            [data-animate], [data-image-reveal] { opacity: 1 !important; transform: none !important; }
            .public-testimonials-rotator [data-testimonial-slide] { opacity: 1 !important; position: relative !important; }
        </style>
    </noscript>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/public.css', 'resources/js/public.js']); ?>
</head>
<body class="font-sans antialiased text-brand-text-primary bg-white public-page">

    <a href="#main-content" class="public-skip-link">Skip to main content</a>

    <div class="public-cmyk-ambient" aria-hidden="true"></div>

    <?php if (isset($component)) { $__componentOriginalcd330711639d87162fe822d546152c9c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcd330711639d87162fe822d546152c9c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.page-loader','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.page-loader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcd330711639d87162fe822d546152c9c)): ?>
<?php $attributes = $__attributesOriginalcd330711639d87162fe822d546152c9c; ?>
<?php unset($__attributesOriginalcd330711639d87162fe822d546152c9c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcd330711639d87162fe822d546152c9c)): ?>
<?php $component = $__componentOriginalcd330711639d87162fe822d546152c9c; ?>
<?php unset($__componentOriginalcd330711639d87162fe822d546152c9c); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal50112f18507694b7d2c52f897a1b2314 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal50112f18507694b7d2c52f897a1b2314 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.scroll-progress','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.scroll-progress'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal50112f18507694b7d2c52f897a1b2314)): ?>
<?php $attributes = $__attributesOriginal50112f18507694b7d2c52f897a1b2314; ?>
<?php unset($__attributesOriginal50112f18507694b7d2c52f897a1b2314); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal50112f18507694b7d2c52f897a1b2314)): ?>
<?php $component = $__componentOriginal50112f18507694b7d2c52f897a1b2314; ?>
<?php unset($__componentOriginal50112f18507694b7d2c52f897a1b2314); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalb146cbf8306c95b172d2591af732a390 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb146cbf8306c95b172d2591af732a390 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.header','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb146cbf8306c95b172d2591af732a390)): ?>
<?php $attributes = $__attributesOriginalb146cbf8306c95b172d2591af732a390; ?>
<?php unset($__attributesOriginalb146cbf8306c95b172d2591af732a390); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb146cbf8306c95b172d2591af732a390)): ?>
<?php $component = $__componentOriginalb146cbf8306c95b172d2591af732a390; ?>
<?php unset($__componentOriginalb146cbf8306c95b172d2591af732a390); ?>
<?php endif; ?>

    <main id="main-content">
        <?php echo e($slot); ?>

    </main>

    <?php if (isset($component)) { $__componentOriginalbb84be681bbe94cc31d6257779433433 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb84be681bbe94cc31d6257779433433 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb84be681bbe94cc31d6257779433433)): ?>
<?php $attributes = $__attributesOriginalbb84be681bbe94cc31d6257779433433; ?>
<?php unset($__attributesOriginalbb84be681bbe94cc31d6257779433433); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb84be681bbe94cc31d6257779433433)): ?>
<?php $component = $__componentOriginalbb84be681bbe94cc31d6257779433433; ?>
<?php unset($__componentOriginalbb84be681bbe94cc31d6257779433433); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal5a5ed36458ef6e351dc7808046466d15 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a5ed36458ef6e351dc7808046466d15 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.conversion-sticky','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.conversion-sticky'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5a5ed36458ef6e351dc7808046466d15)): ?>
<?php $attributes = $__attributesOriginal5a5ed36458ef6e351dc7808046466d15; ?>
<?php unset($__attributesOriginal5a5ed36458ef6e351dc7808046466d15); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5a5ed36458ef6e351dc7808046466d15)): ?>
<?php $component = $__componentOriginal5a5ed36458ef6e351dc7808046466d15; ?>
<?php unset($__componentOriginal5a5ed36458ef6e351dc7808046466d15); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal3cfc746371eb03991c01c501499fbc01 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cfc746371eb03991c01c501499fbc01 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.conversion-exit-intent','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.conversion-exit-intent'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3cfc746371eb03991c01c501499fbc01)): ?>
<?php $attributes = $__attributesOriginal3cfc746371eb03991c01c501499fbc01; ?>
<?php unset($__attributesOriginal3cfc746371eb03991c01c501499fbc01); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3cfc746371eb03991c01c501499fbc01)): ?>
<?php $component = $__componentOriginal3cfc746371eb03991c01c501499fbc01; ?>
<?php unset($__componentOriginal3cfc746371eb03991c01c501499fbc01); ?>
<?php endif; ?>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\layouts\public.blade.php ENDPATH**/ ?>