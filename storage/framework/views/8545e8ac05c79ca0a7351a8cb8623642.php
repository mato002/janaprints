<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ? $title.' — ' : ''); ?><?php echo e(__('Employee Self Service')); ?> — <?php echo e(config('app.name')); ?></title>
    <?php if (isset($component)) { $__componentOriginald9e77967a5438b63fd29d241808e49d9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9e77967a5438b63fd29d241808e49d9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.site-favicon','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('site-favicon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="font-sans antialiased bg-erp-page text-erp-primary ess-portal ess-mobile-shell min-h-screen">
    <header class="sticky top-0 z-30 border-b border-erp-border bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-3xl items-center justify-between gap-3 px-4 py-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-erp-muted"><?php echo e(__('Employee Self Service')); ?></p>
                <h1 class="truncate text-lg font-semibold text-erp-primary"><?php echo e($title ?: __('My Workspace')); ?></h1>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="ess-btn ess-btn--ghost text-sm"><?php echo e(__('Sign out')); ?></button>
            </form>
        </div>
        <nav class="ess-tab-nav mx-auto max-w-3xl overflow-x-auto px-2 pb-2" aria-label="<?php echo e(__('Workspace tabs')); ?>">
            <ul class="flex gap-1">
                <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="shrink-0">
                        <a
                            href="<?php echo e(route('ess.dashboard', ['tab' => $tab['id']])); ?>"
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'ess-tab-link',
                                'ess-tab-link--active' => $activeTab === $tab['id'],
                            ]); ?>"
                        >
                            <?php echo e($tab['label']); ?>

                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </nav>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-4 pb-24">
        <?php if(session('status')): ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <?php echo e(is_string(session('status')) ? session('status') : __('Saved successfully.')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <ul class="list-disc ps-5">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php echo e($slot); ?>

    </main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\layouts\ess.blade.php ENDPATH**/ ?>