<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Client Portal',
    'fullMobileChat' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => 'Client Portal',
    'fullMobileChat' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="turbo-cache-control" content="no-preview">
    <meta name="theme-color" content="#1F237A">
    <?php if(auth()->check() && auth()->user()->isClientPortalAccount()): ?>
        <meta name="client-communications-unread-url" content="<?php echo e(route('client.communications.unread')); ?>">
    <?php endif; ?>
    <title><?php echo e($title); ?> — <?php echo e(config('site.name', config('app.name'))); ?></title>
    <?php if (isset($component)) { $__componentOriginald9e77967a5438b63fd29d241808e49d9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9e77967a5438b63fd29d241808e49d9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.site-favicon','data' => ['url' => $brandingFaviconUrl ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('site-favicon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($brandingFaviconUrl ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald9e77967a5438b63fd29d241808e49d9)): ?>
<?php $attributes = $__attributesOriginald9e77967a5438b63fd29d241808e49d9; ?>
<?php unset($__attributesOriginald9e77967a5438b63fd29d241808e49d9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald9e77967a5438b63fd29d241808e49d9)): ?>
<?php $component = $__componentOriginald9e77967a5438b63fd29d241808e49d9; ?>
<?php unset($__componentOriginald9e77967a5438b63fd29d241808e49d9); ?>
<?php endif; ?>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/public.css', 'resources/js/public.js']); ?>
</head>
<body class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'font-sans antialiased client-portal-page',
    'client-portal-page--chat' => $fullMobileChat,
]); ?>">
    <div class="client-turbo-progress" id="client-turbo-progress" aria-hidden="true"></div>

    <div class="client-portal-wrap">
        <div class="client-sidebar-backdrop" data-client-sidebar-backdrop hidden aria-hidden="true"></div>

        <?php if (isset($component)) { $__componentOriginal714c8a8d2fc75ed292c7bd2d4fee8bbd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal714c8a8d2fc75ed292c7bd2d4fee8bbd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal714c8a8d2fc75ed292c7bd2d4fee8bbd)): ?>
<?php $attributes = $__attributesOriginal714c8a8d2fc75ed292c7bd2d4fee8bbd; ?>
<?php unset($__attributesOriginal714c8a8d2fc75ed292c7bd2d4fee8bbd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal714c8a8d2fc75ed292c7bd2d4fee8bbd)): ?>
<?php $component = $__componentOriginal714c8a8d2fc75ed292c7bd2d4fee8bbd; ?>
<?php unset($__componentOriginal714c8a8d2fc75ed292c7bd2d4fee8bbd); ?>
<?php endif; ?>

        <div class="client-portal-main">
            <?php if (isset($component)) { $__componentOriginal25d997a16a5bece9a4dd849dddbe0d1e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25d997a16a5bece9a4dd849dddbe0d1e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.topbar','data' => ['title' => $heading ?? $title]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heading ?? $title)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25d997a16a5bece9a4dd849dddbe0d1e)): ?>
<?php $attributes = $__attributesOriginal25d997a16a5bece9a4dd849dddbe0d1e; ?>
<?php unset($__attributesOriginal25d997a16a5bece9a4dd849dddbe0d1e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25d997a16a5bece9a4dd849dddbe0d1e)): ?>
<?php $component = $__componentOriginal25d997a16a5bece9a4dd849dddbe0d1e; ?>
<?php unset($__componentOriginal25d997a16a5bece9a4dd849dddbe0d1e); ?>
<?php endif; ?>

            <turbo-frame
                id="client-main"
                data-turbo-action="advance"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'client-portal-frame',
                    'client-portal-frame--chat' => $fullMobileChat,
                ]); ?>"
            >
                <span
                    id="client-route-meta"
                    class="sr-only"
                    data-route="<?php echo e(Route::currentRouteName()); ?>"
                    data-title="<?php echo e($title); ?>"
                    data-heading="<?php echo e($heading ?? $title); ?>"
                    data-full-mobile-chat="<?php echo e($fullMobileChat ? '1' : '0'); ?>"
                    data-app-name="<?php echo e(config('site.name', config('app.name'))); ?>"
                    aria-hidden="true"
                ></span>

                <div class="client-portal-body">
                    <?php if(session('status')): ?>
                        <div class="client-alert client-alert--success client-alert--toast" role="status" data-client-toast>
                            <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'sparkles','class' => 'h-5 w-5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sparkles','class' => 'h-5 w-5 shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $attributes = $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $component = $__componentOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
                            <span><?php echo e(session('status')); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="client-alert client-alert--error" role="alert">
                            <ul class="list-disc ps-5">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <header class="client-page-head hidden lg:block">
                        <div>
                            <p class="client-page-head__eyebrow"><?php echo e(__('My account')); ?></p>
                            <h2 class="client-page-head__title"><?php echo e($heading ?? $title); ?></h2>
                            <?php if(isset($subtitle)): ?>
                                <p class="client-page-head__subtitle"><?php echo e($subtitle); ?></p>
                            <?php endif; ?>
                        </div>
                    </header>

                    <main class="client-content">
                        <?php echo e($slot); ?>

                    </main>
                </div>

                <footer class="client-portal-footer">
                    <div class="client-portal-footer__inner">
                        <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('site.name', 'Jana Prints')); ?>. <?php echo e(__('All rights reserved.')); ?></p>
                        <a href="<?php echo e(route('home')); ?>" class="client-portal-footer__link" data-turbo-frame="_top"><?php echo e(__('Back to website')); ?></a>
                    </div>
                </footer>
            </turbo-frame>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal5ea1e55d2f2aecb377f9d8706cf46005 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5ea1e55d2f2aecb377f9d8706cf46005 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.bottom-nav','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.bottom-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5ea1e55d2f2aecb377f9d8706cf46005)): ?>
<?php $attributes = $__attributesOriginal5ea1e55d2f2aecb377f9d8706cf46005; ?>
<?php unset($__attributesOriginal5ea1e55d2f2aecb377f9d8706cf46005); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5ea1e55d2f2aecb377f9d8706cf46005)): ?>
<?php $component = $__componentOriginal5ea1e55d2f2aecb377f9d8706cf46005; ?>
<?php unset($__componentOriginal5ea1e55d2f2aecb377f9d8706cf46005); ?>
<?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\layouts\client.blade.php ENDPATH**/ ?>