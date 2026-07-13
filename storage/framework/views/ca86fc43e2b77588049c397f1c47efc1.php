<div
    x-show="paletteOpen"
    x-cloak
    class="fixed inset-0 z-[80] flex items-start justify-center px-4 pt-[12vh] sm:px-6"
    @keydown.escape.window="closePalette()"
    @keydown.ctrl.k.window.prevent="openPalette()"
    @keydown.meta.k.window.prevent="openPalette()"
    role="presentation"
>
    <div
        class="absolute inset-0 bg-erp-primary/50 backdrop-blur-[2px]"
        @click="closePalette()"
        aria-hidden="true"
    ></div>

    <div
        class="relative z-10 w-full max-w-3xl overflow-hidden rounded-xl border border-erp-border bg-erp-card shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="erp-command-palette-title"
        @click.stop
    >
        <h2 id="erp-command-palette-title" class="sr-only"><?php echo e(__('Global feature finder')); ?></h2>

        <div class="border-b border-erp-border px-4 py-3">
            <div class="relative">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']); ?>
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
                    id="erp-command-palette-input"
                    type="search"
                    x-ref="paletteInput"
                    x-model="paletteQuery"
                    @keydown.arrow-down.prevent="movePaletteSelection(1)"
                    @keydown.arrow-up.prevent="movePaletteSelection(-1)"
                    @keydown.enter.prevent="openPaletteSelection()"
                    class="erp-input w-full py-2.5 pl-9 pr-24 text-sm"
                    placeholder="<?php echo e(__('Search customers, jobs, reports, settings, features…')); ?>"
                    autocomplete="off"
                    aria-label="<?php echo e(__('Search ERP features')); ?>"
                >
                <div class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 items-center gap-1 sm:flex">
                    <kbd class="rounded border border-erp-border bg-erp-page px-1.5 py-0.5 text-[10px] font-medium text-slate-500">Ctrl</kbd>
                    <kbd class="rounded border border-erp-border bg-erp-page px-1.5 py-0.5 text-[10px] font-medium text-slate-500">K</kbd>
                </div>
            </div>
        </div>

        <div class="max-h-[min(60vh,28rem)] overflow-y-auto py-2" x-ref="paletteResults">
            <template x-if="paletteQuery.trim() === '' && recentItems.length > 0">
                <section class="px-2 pb-2">
                    <p class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Recent')); ?></p>
                    <template x-for="(item, index) in recentItems" :key="`recent-${item.id}`">
                        <div
                            class="group flex items-start gap-2 rounded-lg px-2 py-2"
                            :class="paletteHighlightIndex === index ? 'bg-erp-accent/10' : 'hover:bg-erp-page'"
                            @mouseenter="paletteHighlightIndex = index"
                        >
                            <button
                                type="button"
                                class="min-w-0 flex-1 text-left"
                                @click="navigatePaletteItem(item)"
                            >
                                <span class="block text-sm font-medium text-erp-primary" x-text="item.label"></span>
                                <span class="mt-0.5 block text-xs text-slate-500" x-text="item.path"></span>
                            </button>
                            <div class="flex shrink-0 items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="openPaletteItemNewTab(item)" title="<?php echo e(__('Open in new tab')); ?>">
                                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'external-link','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'external-link','class' => 'h-3.5 w-3.5']); ?>
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
                                <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="copyPaletteItemLink(item)" title="<?php echo e(__('Copy link')); ?>">
                                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'clipboard','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard','class' => 'h-3.5 w-3.5']); ?>
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
                                <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="toggleDiscoveryFavorite(item.id)" title="<?php echo e(__('Favorite')); ?>">
                                    <span x-text="isDiscoveryFavorite(item.id) ? '★' : '☆'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </section>
            </template>

            <template x-if="paletteQuery.trim() === '' && favoriteDiscoveryItems.length > 0">
                <section class="px-2 pb-2">
                    <p class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Favorites')); ?></p>
                    <template x-for="(item, index) in favoriteDiscoveryItems" :key="`fav-${item.id}`">
                        <div
                            class="group flex items-start gap-2 rounded-lg px-2 py-2"
                            :class="paletteHighlightIndex === (recentItems.length + index) ? 'bg-erp-accent/10' : 'hover:bg-erp-page'"
                            @mouseenter="paletteHighlightIndex = recentItems.length + index"
                        >
                            <button
                                type="button"
                                class="min-w-0 flex-1 text-left"
                                @click="navigatePaletteItem(item)"
                            >
                                <span class="block text-sm font-medium text-erp-primary">
                                    <span class="text-amber-500">★</span>
                                    <span x-text="item.label"></span>
                                </span>
                                <span class="mt-0.5 block text-xs text-slate-500" x-text="item.path"></span>
                            </button>
                        </div>
                    </template>
                </section>
            </template>

            <template x-if="paletteQuery.trim() !== '' && paletteLoading">
                <p class="px-4 py-8 text-center text-sm text-slate-500"><?php echo e(__('Searching…')); ?></p>
            </template>

            <template x-for="section in paletteSections" :key="section.key">
                <section class="px-2 pb-2">
                    <p class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500" x-text="section.label"></p>
                    <template x-for="(item, index) in section.items" :key="item.id">
                        <div
                            class="group flex items-start gap-2 rounded-lg px-2 py-2"
                            :class="paletteHighlightIndex === paletteSectionOffset(section.key, index) ? 'bg-erp-accent/10' : 'hover:bg-erp-page'"
                            @mouseenter="paletteHighlightIndex = paletteSectionOffset(section.key, index)"
                        >
                            <button
                                type="button"
                                class="min-w-0 flex-1 text-left"
                                @click="navigatePaletteItem(item)"
                            >
                                <span class="block text-sm font-medium text-erp-primary" x-text="item.label"></span>
                                <span class="mt-0.5 block text-xs text-slate-500" x-text="item.path"></span>
                                <span
                                    x-show="item.description"
                                    class="mt-1 block text-xs text-slate-400"
                                    x-text="item.description"
                                ></span>
                            </button>
                            <div class="flex shrink-0 items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="openPaletteItemNewTab(item)" title="<?php echo e(__('Open in new tab')); ?>">
                                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'external-link','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'external-link','class' => 'h-3.5 w-3.5']); ?>
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
                                <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="copyPaletteItemLink(item)" title="<?php echo e(__('Copy link')); ?>">
                                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'clipboard','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard','class' => 'h-3.5 w-3.5']); ?>
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
                                <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="toggleDiscoveryFavorite(item.id)" title="<?php echo e(__('Favorite')); ?>">
                                    <span x-text="isDiscoveryFavorite(item.id) ? '★' : '☆'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </section>
            </template>

            <p
                x-show="paletteQuery.trim() !== '' && ! paletteLoading && paletteFlatResults.length === 0"
                class="px-4 py-8 text-center text-sm text-slate-500"
            >
                <?php echo e(__('No features found. Try a different keyword.')); ?>

            </p>
        </div>

        <div class="flex items-center justify-between border-t border-erp-border bg-erp-page/60 px-4 py-2 text-[11px] text-slate-500">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-erp-border px-1">↑</kbd><kbd class="rounded border border-erp-border px-1">↓</kbd> <?php echo e(__('Navigate')); ?></span>
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-erp-border px-1">↵</kbd> <?php echo e(__('Open')); ?></span>
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-erp-border px-1">Esc</kbd> <?php echo e(__('Close')); ?></span>
            </div>
            <span><?php echo e(__('Feature search — not record search')); ?></span>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/components/admin/command-palette.blade.php ENDPATH**/ ?>