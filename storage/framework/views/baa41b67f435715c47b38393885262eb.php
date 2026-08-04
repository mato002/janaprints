<?php
    $navByRoute = collect(config('client_portal.nav', []))->keyBy('route');
    $bottomRoutes = array_slice(config('client_portal.bottom_nav_routes', []), 0, 3);
    $bottomLabels = config('client_portal.bottom_nav_labels', []);

    $items = collect($bottomRoutes)
        ->map(function (string $route) use ($navByRoute, $bottomLabels) {
            $nav = $navByRoute->get($route);

            if (! $nav || ! Route::has($route)) {
                return null;
            }

            $activeRoutes = $nav['active_routes'] ?? [$route, $route.'.*'];

            return [
                'label' => __($bottomLabels[$route] ?? $nav['label']),
                'route' => $route,
                'icon' => $nav['icon'] ?? 'home',
                'match' => $activeRoutes,
                'badge' => $route === 'client.communications.index',
            ];
        })
        ->filter()
        ->values()
        ->all();

    $primaryMatchPatterns = collect($items)
        ->flatMap(fn (array $item) => $item['match'])
        ->unique()
        ->values()
        ->all();
?>

<nav
    class="client-bottom-nav lg:hidden"
    aria-label="<?php echo e(__('Quick navigation')); ?>"
    data-client-bottom-nav
    data-client-bottom-nav-primary="<?php echo e(implode(',', $bottomRoutes)); ?>"
    data-client-bottom-nav-primary-patterns="<?php echo e(implode('|', $primaryMatchPatterns)); ?>"
    style="--client-bottom-nav-cols: <?php echo e(count($items) + 1); ?>"
>
    <div class="client-bottom-nav__inner">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $active = collect($item['match'])->contains(fn (string $pattern) => request()->routeIs($pattern));
            ?>
            <a
                href="<?php echo e(route($item['route'])); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses(['client-bottom-nav__link', 'is-active' => $active]); ?>"
                data-client-bottom-nav-link
                data-client-nav-route="<?php echo e($item['route']); ?>"
                data-client-nav-active="<?php echo e(implode(',', $item['match'])); ?>"
                data-turbo-frame="client-main"
                data-turbo-action="advance"
            >
                <span class="client-bottom-nav__icon-wrap">
                    <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => $item['icon'],'class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['icon']),'class' => 'h-5 w-5']); ?>
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
                    <?php if(($item['badge'] ?? false) && ($clientCommunicationsUnread ?? 0) > 0): ?>
                        <span
                            class="client-bottom-nav__badge"
                            data-client-comms-unread-badge
                        ><?php echo e(($clientCommunicationsUnread ?? 0) > 9 ? '9+' : $clientCommunicationsUnread); ?></span>
                    <?php endif; ?>
                </span>
                <span class="client-bottom-nav__label"><?php echo e($item['label']); ?></span>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php
            $onPrimaryBottomNav = collect($items)->contains(
                fn (array $item) => collect($item['match'])->contains(fn (string $pattern) => request()->routeIs($pattern))
            );
        ?>
        <button
            type="button"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses(['client-bottom-nav__link', 'is-active' => ! $onPrimaryBottomNav]); ?>"
            data-client-bottom-nav-more
            data-client-sidebar-toggle
            aria-expanded="false"
            aria-controls="client-sidebar"
        >
            <span class="client-bottom-nav__icon-wrap">
                <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'menu','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'menu','class' => 'h-5 w-5']); ?>
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
            </span>
            <span class="client-bottom-nav__label"><?php echo e(__('More')); ?></span>
        </button>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\client\bottom-nav.blade.php ENDPATH**/ ?>