<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="turbo-cache-control" content="no-preview">
    <title><?php echo e($title ? $title.' — ' : ''); ?><?php echo e(config('app.name')); ?></title>
    <?php if (isset($component)) { $__componentOriginald9e77967a5438b63fd29d241808e49d9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9e77967a5438b63fd29d241808e49d9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.site-favicon','data' => ['url' => $brandingFaviconUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('site-favicon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($brandingFaviconUrl)]); ?>
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
    <?php
        $erpModalFormConfig = [
            'backLabel' => __('Back'),
            'blockedPathFragments' => [
                '/commercial/pos/',
                'counter-sales',
                '/dashboard',
                '/login',
                '/logout',
            ],
        ];
    ?>
    <script>
        window.__erpRoutes = <?php echo json_encode($navRouteUrls ?? [], 15, 512) ?>;
        window.__erpModalForm = <?php echo json_encode($erpModalFormConfig, 15, 512) ?>;
        window.__erpFeatureDiscovery = <?php echo json_encode(['searchUrl' => $featureDiscoverySearchUrl ?? ''], 15, 512) ?>;
        window.__erpTableExportUrl = <?php echo json_encode(route('admin.exports.table'), 15, 512) ?>;
    </script>
</head>
<body
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'font-sans antialiased bg-erp-page text-erp-primary overflow-hidden',
    ]); ?>"
    style="--erp-sticky-table-offset: <?php echo e($compactPage ? '6.5rem' : ($compactWorkspace ? '10.5rem' : '12rem')); ?>"
    x-data="erpShell()"
    @keydown.escape.window="if (paletteOpen) { closePalette(); } else { closeMobileNav(); }"
    @close-nav.window="closeMobileNav()"
>
    <div class="turbo-progress" id="turbo-progress" aria-hidden="true"></div>

    <div
        x-show="mobileNavOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-erp-primary/60 lg:hidden"
        @click="closeMobileNav()"
        x-cloak
    ></div>

    <?php echo $__env->make('layouts.admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div
        id="erp-app-shell"
        class="flex h-screen max-h-screen min-w-0 flex-col overflow-hidden transition-[margin-left] duration-sidebar max-lg:ml-0"
        :class="sidebarCollapsed ? 'lg:ml-sidebar-collapsed' : 'lg:ml-sidebar'"
    >
        <?php echo $__env->make('layouts.admin.partials.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <turbo-frame
            id="erp-main"
            data-turbo-action="advance"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'flex min-h-0 flex-1 flex-col',
                'overflow-hidden' => $compactPage || $compactWorkspace,
                'overflow-x-auto overflow-y-auto' => ! $compactPage && ! $compactWorkspace,
            ]); ?>"
        >
            <?php
                $frameQuickCreate = array_values(array_map(
                    fn (array $item) => [
                        'label' => $item['label'],
                        'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                        'modal' => (bool) ($item['modal'] ?? false),
                        'href' => empty($item['coming_soon']) && ! empty($item['route']) && Route::has($item['route'])
                            ? route($item['route'], $item['route_params'] ?? [])
                            : null,
                    ],
                    array_filter(
                        app(\App\Support\Navigation\WorkspacePresenter::class)->quickCreateForRoute(Route::currentRouteName()),
                        fn (array $item) => $item['visible'] ?? true,
                    ),
                ));
            ?>
            <span
                id="erp-route-meta"
                class="sr-only"
                data-route="<?php echo e(Route::currentRouteName()); ?>"
                data-title="<?php echo e($title); ?>"
                data-compact-page="<?php echo e($compactPage ? '1' : '0'); ?>"
                data-app-name="<?php echo e(config('app.name')); ?>"
                data-quick-create="<?php echo e(json_encode($frameQuickCreate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)); ?>"
                data-i18n-create="<?php echo e(__('Create')); ?>"
                data-i18n-soon="<?php echo e(__('Soon')); ?>"
                aria-hidden="true"
            ></span>
            <main class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'flex min-h-0 min-w-0 flex-1 flex-col',
                'overflow-hidden p-2' => $compactPage,
                'overflow-hidden p-2 sm:p-3' => ! $compactPage && $compactWorkspace,
                'p-4 sm:p-6 lg:p-8' => ! $compactPage && ! $compactWorkspace,
            ]); ?>">
                <?php if (! ($compactPage || $compactWorkspace)): ?>
                    <?php echo $__env->make('admin.partials.breadcrumbs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
                
                <?php echo $__env->make('admin.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php if(isset($header)): ?>
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <?php echo e($header); ?>

                    </div>
                <?php endif; ?>

                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'workspace-wrapper flex min-h-0 min-w-0 flex-1 flex-col',
                    'overflow-hidden' => $compactPage || $compactWorkspace,
                ]); ?>">
                    <?php echo e($slot); ?>

                </div>
            </main>
        </turbo-frame>
    </div>

    <div
        id="erp-modal-overlay"
        class="erp-modal-overlay"
        data-erp-modal-overlay
        data-turbo-permanent
        hidden
        aria-hidden="true"
    >
        <div class="erp-modal-overlay__backdrop" data-erp-form-modal-close tabindex="-1" aria-hidden="true"></div>
        <div class="erp-modal-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="erp-form-modal-title">
            <div id="erp-form-modal" class="erp-form-modal-host"></div>
        </div>
    </div>

    <div
        id="erp-lookup-modal-overlay"
        class="erp-lookup-modal-overlay"
        data-erp-lookup-modal-overlay
        data-turbo-permanent
        hidden
        aria-hidden="true"
    >
        <div class="erp-lookup-modal-overlay__backdrop" data-erp-lookup-modal-close tabindex="-1" aria-hidden="true"></div>
        <div class="erp-lookup-modal-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="erp-lookup-modal-title">
            <div id="erp-lookup-modal" class="erp-form-modal-host"></div>
        </div>
    </div>

    <div
        id="erp-drawer-overlay"
        class="erp-drawer-overlay"
        data-erp-drawer-overlay
        data-turbo-permanent
        hidden
        aria-hidden="true"
    >
        <div class="erp-drawer-overlay__backdrop" data-erp-drawer-close tabindex="-1" aria-hidden="true"></div>
        <turbo-frame id="erp-preview-drawer"></turbo-frame>
    </div>

    <div id="erp-toast-host" class="erp-toast-host" data-turbo-permanent aria-live="polite"></div>

    <?php if (isset($component)) { $__componentOriginalb774badd5a25171b3116df46663b1b78 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb774badd5a25171b3116df46663b1b78 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.command-palette','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.command-palette'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb774badd5a25171b3116df46663b1b78)): ?>
<?php $attributes = $__attributesOriginalb774badd5a25171b3116df46663b1b78; ?>
<?php unset($__attributesOriginalb774badd5a25171b3116df46663b1b78); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb774badd5a25171b3116df46663b1b78)): ?>
<?php $component = $__componentOriginalb774badd5a25171b3116df46663b1b78; ?>
<?php unset($__componentOriginalb774badd5a25171b3116df46663b1b78); ?>
<?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/layouts/admin.blade.php ENDPATH**/ ?>