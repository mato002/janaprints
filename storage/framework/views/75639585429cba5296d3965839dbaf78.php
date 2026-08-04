<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'searchable' => true,
    'searchPlaceholder' => null,
    'exportable' => true,
    'selectable' => false,
    'filterable' => null,
    'exportFilename' => 'export',
    'exportCsvUrl' => null,
    'exportExcelUrl' => null,
    'exportPdfUrl' => null,
    'exportRoute' => null,
    'exportQuery' => null,
    'exportRouteParams' => [],
    'formatInPath' => false,
    'exportPostAction' => null,
    'exportPostFields' => [],
    'exportPostFormats' => null,
    'canExport' => true,
    'chips' => [],
    'tableId' => null,
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
    'searchable' => true,
    'searchPlaceholder' => null,
    'exportable' => true,
    'selectable' => false,
    'filterable' => null,
    'exportFilename' => 'export',
    'exportCsvUrl' => null,
    'exportExcelUrl' => null,
    'exportPdfUrl' => null,
    'exportRoute' => null,
    'exportQuery' => null,
    'exportRouteParams' => [],
    'formatInPath' => false,
    'exportPostAction' => null,
    'exportPostFields' => [],
    'exportPostFormats' => null,
    'canExport' => true,
    'chips' => [],
    'tableId' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $searchPlaceholder ??= __('Search…');
    $tableId ??= 'erp-table-'.Str::random(6);
    $showFilters = $filterable ?? isset($filters);
    $chipPayload = collect($chips)->map(fn ($chip) => [
        'id' => $chip['id'] ?? $chip['label'] ?? 'all',
        'label' => $chip['label'] ?? $chip['id'] ?? 'All',
    ])->values()->all();

    if ($chipPayload === []) {
        $chipPayload = [['id' => 'all', 'label' => __('All')]];
    }

    $hasServerExport = filled($exportRoute) || filled($exportCsvUrl) || filled($exportExcelUrl) || filled($exportPdfUrl) || filled($exportPostAction);
    $hasClientExport = ! $hasServerExport;

    $gridConfig = [
        'exportFilename' => $exportFilename,
        'chips' => $chipPayload,
        'selectable' => $selectable,
        'tableId' => $tableId,
        'hasClientExport' => $hasClientExport,
        'brandingLogoUrl' => app(\App\Support\Branding\BrandingAssets::class)->logoUrl(),
        'tableExportUrl' => route('admin.exports.table'),
    ];
?>

<div
    x-data="erpDataTable(<?php echo \Illuminate\Support\Js::from($gridConfig)->toHtml() ?>)"
    <?php echo e($attributes->merge(['class' => 'erp-data-grid w-full min-w-0'])); ?>

>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'min-w-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'min-w-0']); ?>
        <?php if($searchable || $showFilters || $exportable || $selectable || isset($toolbar) || isset($bulk) || count($chipPayload) > 1): ?>
            <details class="workspace-filter-panel">
                <summary><?php echo e(__('Search & filters')); ?></summary>
                <div class="erp-table-toolbar border-b border-erp-border bg-white px-3 py-3 sm:px-4">
                <div class="erp-table-toolbar__layout flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="erp-table-toolbar__primary flex min-w-0 flex-1 flex-wrap items-center gap-2">
                        <?php if(count($chipPayload) > 1): ?>
                            <div class="erp-table-chips flex flex-wrap gap-1.5">
                                <?php $__currentLoopData = $chipPayload; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button
                                        type="button"
                                        class="erp-filter-pill"
                                        :class="activeChip === <?php echo \Illuminate\Support\Js::from($chip['id'])->toHtml() ?> ? 'erp-filter-pill--active' : ''"
                                        @click="setChip(<?php echo \Illuminate\Support\Js::from($chip['id'])->toHtml() ?>)"
                                    >
                                        <?php echo e($chip['label']); ?>

                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>

                        <?php if($searchable): ?>
                            <div class="erp-table-toolbar__search relative min-w-0 w-full flex-1 sm:min-w-[12rem] sm:max-w-md">
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
                                    type="search"
                                    x-model="query"
                                    placeholder="<?php echo e($searchPlaceholder); ?>"
                                    class="erp-input w-full py-2 pl-9 text-sm"
                                    aria-label="<?php echo e(__('Search table')); ?>"
                                />
                            </div>
                        <?php endif; ?>

                        <?php if($showFilters && isset($filters)): ?>
                            <?php echo e($filters); ?>

                        <?php endif; ?>

                        <label class="flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span><?php echo e(__('Rows')); ?></span>
                            <select class="erp-select py-1 text-xs" x-model.number="pageSize" @change="setPageSize(pageSize)">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </label>

                        <?php echo e($toolbar ?? ''); ?>

                    </div>

                    <div class="erp-table-toolbar__secondary flex w-full shrink-0 flex-wrap items-center justify-between gap-2 sm:w-auto sm:justify-end">
                        <div
                            x-show="selectable && selectedCount > 0"
                            x-cloak
                            class="flex flex-wrap items-center gap-2 rounded-lg border border-erp-accent/20 bg-erp-accent/5 px-2 py-1"
                        >
                            <span class="text-xs font-medium text-erp-primary" x-text="`${selectedCount} <?php echo e(__('selected')); ?>`"></span>
                            <?php if(isset($bulk)): ?>
                                <?php echo e($bulk); ?>

                            <?php else: ?>
                                <button type="button" class="erp-btn-ghost py-1 text-xs" @click="exportSelected()"><?php echo e(__('Export selected')); ?></button>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-1 rounded-lg border border-erp-border bg-white p-1">
                            <button type="button" class="erp-btn-ghost py-1 text-xs" @click="previousPage()" :disabled="currentPage <= 1" :class="currentPage <= 1 ? 'opacity-40' : ''"><?php echo e(__('Prev')); ?></button>
                            <span class="px-2 text-xs font-medium text-slate-500" x-text="currentPage"></span>
                            <button type="button" class="erp-btn-ghost py-1 text-xs" @click="nextPage()"><?php echo e(__('Next')); ?></button>
                        </div>

                        <?php if($exportable): ?>
                            <?php if($hasServerExport): ?>
                                <?php if (isset($component)) { $__componentOriginalf419e868e892b32e6daa894c958d94bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf419e868e892b32e6daa894c958d94bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.export-dropdown','data' => ['exportRoute' => $exportRoute,'exportQuery' => $exportQuery,'exportRouteParams' => $exportRouteParams,'formatInPath' => $formatInPath,'csvUrl' => $exportCsvUrl,'excelUrl' => $exportExcelUrl,'pdfUrl' => $exportPdfUrl,'postAction' => $exportPostAction,'postFields' => $exportPostFields,'canExport' => $canExport]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.export-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['export-route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportRoute),'export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportQuery),'export-route-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportRouteParams),'format-in-path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($formatInPath),'csv-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportCsvUrl),'excel-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportExcelUrl),'pdf-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportPdfUrl),'post-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportPostAction),'post-fields' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportPostFields),'can-export' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canExport)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $attributes = $__attributesOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__attributesOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $component = $__componentOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__componentOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
                            <?php else: ?>
                                <div class="relative" @click.outside="exportOpen = false">
                                    <button
                                        type="button"
                                        class="erp-toolbar-export-btn erp-btn-secondary py-2 text-sm"
                                        :disabled="exportLoading"
                                        @click.stop="!exportLoading && (exportOpen = !exportOpen)"
                                        title="<?php echo e(__('Export')); ?>"
                                    >
                                        <span x-show="!exportLoading" class="inline-flex items-center gap-2">
                                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'download','class' => 'h-4 w-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'download','class' => 'h-4 w-4 shrink-0']); ?>
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
                                            <span class="erp-toolbar-export-btn__label"><?php echo e(__('Export')); ?></span>
                                        </span>
                                        <span x-show="exportLoading" x-cloak class="inline-flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            <span class="erp-toolbar-export-btn__label hidden sm:inline"><?php echo e(__('Exporting…')); ?></span>
                                        </span>
                                    </button>
                                    <div
                                        x-show="exportOpen && !exportLoading"
                                        x-cloak
                                        class="absolute end-0 z-20 mt-1 min-w-[10rem] rounded-lg border border-erp-border bg-white py-1 shadow-lg"
                                    >
                                        <button type="button" class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page" @click.stop="exportTable('csv')"><?php echo e(__('Export CSV')); ?></button>
                                        <button type="button" class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page" @click.stop="exportTable('excel')"><?php echo e(__('Export Excel')); ?></button>
                                        <button type="button" class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page" @click.stop="exportTable('pdf')"><?php echo e(__('Export PDF')); ?></button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php echo e($actions ?? ''); ?>

                    </div>
                </div>
                </div>
            </details>
        <?php endif; ?>

        <div class="erp-table-scroll max-w-full">
            <table id="<?php echo e($tableId); ?>" class="erp-table erp-table--grid">
                <?php if(isset($head)): ?>
                    <thead><?php echo e($head); ?></thead>
                <?php endif; ?>
                <?php if(isset($body)): ?>
                    <tbody><?php echo e($body); ?></tbody>
                <?php else: ?>
                    <tbody><?php echo e($slot); ?></tbody>
                <?php endif; ?>
            </table>
            <div x-show="showNoResults" x-cloak class="border-t border-erp-border bg-white px-4 py-8 text-center text-sm text-slate-500">
                <?php echo e(__('No rows match your search or filters.')); ?>

            </div>
        </div>

        <?php if(isset($footer)): ?>
            <div class="border-t border-erp-border bg-white">
                <?php echo e($footer); ?>

            </div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\data-table.blade.php ENDPATH**/ ?>