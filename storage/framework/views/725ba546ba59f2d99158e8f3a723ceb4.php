<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'moduleTitle',
    'moduleKey' => null,
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
    'moduleTitle',
    'moduleKey' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    x-data="moduleWorkspaceSearch(<?php echo \Illuminate\Support\Js::from($moduleKey)->toHtml() ?>)"
    class="module-workspace-search workspace-search-bar"
>
    <div class="module-workspace-search__input-wrap relative w-full sm:w-56 lg:w-64">
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400']); ?>
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
            type="search"
            x-model="query"
            @input="scheduleSearch()"
            @focus="open = true"
            @keydown.escape="clear()"
            class="erp-input module-workspace-search__input w-full py-1.5 pl-8 pr-3 text-sm"
            placeholder="<?php echo e(__('Search :module…', ['module' => $moduleTitle])); ?>"
            autocomplete="off"
            aria-label="<?php echo e(__('Search module features')); ?>"
        >
    </div>

    <div
        x-show="open && query.trim()"
        x-cloak
        class="module-workspace-search__dropdown absolute right-0 z-30 mt-1 max-h-56 w-full min-w-[16rem] overflow-y-auto rounded-lg border border-erp-border bg-erp-card shadow-lg sm:w-72"
    >
        <template x-if="loading">
            <p class="px-3 py-4 text-center text-sm text-slate-500"><?php echo e(__('Searching…')); ?></p>
        </template>
        <template x-if="! loading">
            <div>
                <template x-for="hit in hits" :key="hit.id">
                    <a
                        :href="hit.url"
                        :data-turbo-frame="hit.turbo_frame || 'module-workspace-content'"
                        data-turbo-action="advance"
                        @click="clear()"
                        class="module-workspace-search__hit block border-b border-erp-border px-3 py-2 last:border-0 hover:bg-erp-page"
                    >
                        <span class="block text-sm font-medium text-erp-primary" x-text="hit.label"></span>
                        <span class="mt-0.5 block text-xs text-slate-500" x-text="hit.path"></span>
                        <span
                            x-show="hit.description"
                            class="mt-0.5 block text-xs text-slate-400"
                            x-text="hit.description"
                        ></span>
                    </a>
                </template>
                <p x-show="hits.length === 0" class="px-3 py-4 text-center text-sm text-slate-500">
                    <?php echo e(__('No results found.')); ?>

                </p>
            </div>
        </template>
    </div>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/components/admin/module-workspace-search.blade.php ENDPATH**/ ?>