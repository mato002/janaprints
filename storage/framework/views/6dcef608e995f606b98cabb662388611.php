<?php
    $navItems = collect(config('client_portal.nav', []))
        ->reject(fn (array $item) => ($item['route'] ?? '') === 'client.account.edit');
    $user = auth()->user();
    $initials = collect(explode(' ', $user?->name ?? ''))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
?>

<aside id="client-sidebar" class="client-sidebar" data-client-sidebar aria-label="<?php echo e(__('Account navigation')); ?>">
    <div class="client-sidebar__head">
        <a
            href="<?php echo e(route('client.dashboard')); ?>"
            class="client-sidebar__brand"
            data-turbo-frame="client-main"
            data-turbo-action="advance"
        >
            <img
                src="<?php echo e($brandingSidebarLogoUrl); ?>"
                alt=""
                class="client-sidebar__brand-logo"
                width="36"
                height="36"
                decoding="async"
                aria-hidden="true"
            >
            <span class="client-sidebar__brand-name"><?php echo e(config('site.name', config('app.name'))); ?></span>
        </a>

        <div class="client-sidebar__profile lg:hidden">
            <span class="client-sidebar__profile-avatar" aria-hidden="true"><?php echo e($initials ?: 'C'); ?></span>
            <div class="client-sidebar__profile-text">
                <p class="client-sidebar__profile-name"><?php echo e($user?->name); ?></p>
                <p class="client-sidebar__profile-company"><?php echo e($user?->customer?->company_name); ?></p>
            </div>
        </div>

        <p class="client-sidebar__label hidden lg:block"><?php echo e(__('My account')); ?></p>
    </div>

    <nav class="client-sidebar__nav">
        <?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $activeRoutes = $item['active_routes'] ?? [$item['route'].'*'];
                $active = collect($activeRoutes)->contains(fn (string $pattern) => request()->routeIs($pattern))
                    || (($item['route'] ?? '') === 'client.dashboard' && request()->routeIs('client.dashboard'));
            ?>
            <a
                href="<?php echo e(route($item['route'])); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses(['client-sidebar__link', 'is-active' => $active]); ?>"
                data-client-sidebar-link
                data-client-nav-route="<?php echo e($item['route']); ?>"
                data-client-nav-active="<?php echo e(implode(',', $activeRoutes)); ?>"
                data-turbo-frame="client-main"
                data-turbo-action="advance"
            >
                <span class="client-sidebar__icon">
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
                </span>
                <span><?php echo e(__($item['label'])); ?></span>
                <?php if(($item['route'] ?? '') === 'client.communications.index' && ($clientCommunicationsUnread ?? 0) > 0): ?>
                    <span
                        class="client-sidebar__badge"
                        data-client-comms-unread-badge
                        aria-label="<?php echo e(__(':count unread messages', ['count' => $clientCommunicationsUnread])); ?>"
                    ><?php echo e($clientCommunicationsUnread > 99 ? '99+' : $clientCommunicationsUnread); ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <div class="client-sidebar__foot lg:hidden">
        <a
            href="<?php echo e(route('client.account.edit')); ?>"
            class="client-sidebar__foot-link"
            data-turbo-frame="client-main"
            data-turbo-action="advance"
        >
            <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'user','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','class' => 'h-4 w-4']); ?>
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
            <?php echo e(__('Account settings')); ?>

        </a>
        <form method="POST" action="<?php echo e(route('logout')); ?>" data-turbo-frame="_top">
            <?php echo csrf_field(); ?>
            <button type="submit" class="client-sidebar__foot-link client-sidebar__foot-link--danger">
                <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'logout','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'logout','class' => 'h-4 w-4']); ?>
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
                <?php echo e(__('Sign out')); ?>

            </button>
        </form>
    </div>
</aside>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\client\sidebar.blade.php ENDPATH**/ ?>