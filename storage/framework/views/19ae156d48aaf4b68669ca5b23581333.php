<?php
    use App\Providers\AppServiceProvider as Nav;
?>

<aside
    id="erp-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-erp-sidebar text-slate-200 transition-all duration-sidebar -translate-x-full lg:translate-x-0"
    :class="[
        mobileNavOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-sidebar-collapsed' : 'w-sidebar',
    ]"
    aria-label="<?php echo e(__('Main navigation')); ?>"
>
    <div
        class="flex h-16 shrink-0 items-center gap-2 border-b border-white/10 px-3 lg:px-4"
        :class="sidebarCollapsed ? 'lg:h-auto lg:flex-col lg:justify-center lg:gap-2 lg:py-3 lg:px-2' : ''"
    >
        <button
            type="button"
            @click="toggleSidebar()"
            class="hidden shrink-0 rounded-lg p-2 text-slate-400 transition-colors hover:bg-white/10 hover:text-white lg:inline-flex"
            :class="sidebarCollapsed ? 'lg:order-1' : 'lg:order-none'"
            :aria-label="sidebarCollapsed ? '<?php echo e(__('Expand sidebar')); ?>' : '<?php echo e(__('Collapse sidebar')); ?>'"
        >
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chevron-left','class' => 'h-5 w-5 transition-transform duration-sidebar',':class' => 'sidebarCollapsed ? \'rotate-180\' : \'\'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-left','class' => 'h-5 w-5 transition-transform duration-sidebar',':class' => 'sidebarCollapsed ? \'rotate-180\' : \'\'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
        </button>

        <a
            href="<?php echo e($adminHomeUrl ?? route('admin.dashboard')); ?>"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            data-turbo-preload="hover"
            class="flex min-w-0 flex-1 items-center gap-3 font-semibold tracking-tight text-white"
            :class="sidebarCollapsed ? 'lg:order-2 lg:flex-none lg:justify-center' : ''"
            @click="$dispatch('close-nav')"
        >
            <img
                src="<?php echo e($brandingSidebarLogoUrl); ?>"
                alt=""
                class="h-9 w-9 shrink-0 rounded-lg object-contain bg-white"
                width="36"
                height="36"
                decoding="async"
                aria-hidden="true"
            >
            <span class="truncate text-base" x-show="!sidebarCollapsed" x-cloak><?php echo e(config('app.name')); ?></span>
        </a>

        <button
            type="button"
            class="ml-auto shrink-0 rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden"
            @click="closeMobileNav()"
            aria-label="<?php echo e(__('Close menu')); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chevron-left','class' => 'h-5 w-5 rotate-180']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-left','class' => 'h-5 w-5 rotate-180']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
        </button>
    </div>

    <div x-show="!sidebarCollapsed" x-cloak class="shrink-0 space-y-2 border-b border-white/10 px-3 py-3">
        <label class="sr-only" for="nav-search"><?php echo e(__('Search navigation')); ?></label>
        <div class="relative">
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
            <input
                id="nav-search"
                type="search"
                x-model="query"
                @focus="searchOpen = true"
                @keydown.escape="clearSearch()"
                class="w-full rounded-lg border-0 bg-white/10 py-2 pl-8 pr-3 text-sm text-white placeholder:text-slate-500 focus:bg-white/15 focus:ring-2 focus:ring-erp-accent/40"
                placeholder="<?php echo e(__('Search features…')); ?>"
                autocomplete="off"
            >
        </div>

        <div x-show="searchOpen && query.trim()" x-cloak class="max-h-48 overflow-y-auto rounded-lg border border-white/10 bg-erp-primary/90 shadow-lg">
            <p x-show="sidebarLoading" class="px-3 py-3 text-center text-xs text-slate-400"><?php echo e(__('Searching…')); ?></p>
            <template x-for="hit in searchHits" :key="hit.id">
                <a
                    x-show="! hit.coming_soon"
                    :href="hit.url"
                    data-turbo-frame="erp-main"
                    data-turbo-action="advance"
                    @click="clearSearch(); $dispatch('close-nav')"
                    class="block border-b border-white/5 px-3 py-2 text-sm text-slate-200 last:border-0 hover:bg-white/10"
                >
                    <span class="block font-medium" x-text="hit.label"></span>
                    <span class="block text-xs text-slate-500" x-text="hit.path"></span>
                </a>
            </template>
            <p x-show="searchHits.length === 0" class="px-3 py-4 text-center text-xs text-slate-500"><?php echo e(__('No matches')); ?></p>
        </div>

        <div x-show="favoriteItems.length > 0 && !query.trim()" x-cloak>
            <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Pinned')); ?></p>
            <div class="flex flex-wrap gap-1">
                <template x-for="fav in favoriteItems" :key="fav.route">
                    <a
                        :href="fav.url"
                        data-turbo-frame="erp-main"
                        class="inline-flex items-center gap-1 rounded-md bg-white/10 px-2 py-1 text-xs text-slate-200 hover:bg-erp-primary hover:text-white"
                        :title="fav.path"
                        @click="$dispatch('close-nav')"
                    >
                        <span x-text="fav.label"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-2 py-3" x-show="!query.trim() || sidebarCollapsed">
        <?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $active = Nav::navItemIsActive($item); ?>
            <a
                href="<?php echo e(route($item['route'])); ?>"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
                data-turbo-preload="hover"
                data-nav-route="<?php echo e($item['route']); ?>"
                <?php if(! empty($item['active_routes'])): ?>
                    data-nav-active-routes="<?php echo e(implode(',', $item['active_routes'])); ?>"
                <?php endif; ?>
                @click="$dispatch('close-nav')"
                class="mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors erp-nav-link <?php echo e($active ? 'erp-nav-link--active border-l-3 border-erp-accent bg-erp-primary text-white' : 'text-slate-200 hover:text-white'); ?>"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                title="<?php echo e($item['label']); ?>"
            >
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $item['icon'] ?? 'home','class' => 'h-5 w-5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['icon'] ?? 'home'),'class' => 'h-5 w-5 shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                <span class="truncate" x-show="!sidebarCollapsed" x-cloak><?php echo e($item['label']); ?></span>
                <?php if(! empty($item['badge_count']) && (int) $item['badge_count'] > 0): ?>
                    <span class="erp-nav-badge erp-nav-badge--quote" x-show="!sidebarCollapsed" x-cloak><?php echo e($item['badge_count']); ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
</aside>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\layouts\admin\partials\sidebar.blade.php ENDPATH**/ ?>