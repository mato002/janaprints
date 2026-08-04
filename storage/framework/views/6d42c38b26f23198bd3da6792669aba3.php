<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
    'exportRoute' => null,
    'exportQuery' => null,
    'exportRouteParams' => [],
    'formatInPath' => false,
    'postAction' => null,
    'postFields' => [],
    'canExport' => true,
    'disabledTitle' => null,
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
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
    'exportRoute' => null,
    'exportQuery' => null,
    'exportRouteParams' => [],
    'formatInPath' => false,
    'postAction' => null,
    'postFields' => [],
    'canExport' => true,
    'disabledTitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $postFields = collect($postFields)->filter(fn ($value) => $value !== null && $value !== '')->all();
    $formatLabels = [
        'csv' => __('Export CSV'),
        'excel' => __('Export Excel'),
        'pdf' => __('Export PDF'),
    ];
    $formats = ['csv', 'excel', 'pdf'];
    $query = $exportQuery ?? request()->query();
    $routeParams = $exportRouteParams ?? [];
    $availableFormats = [];

    if ($canExport && filled($exportRoute)) {
        foreach ($formats as $format) {
            $params = array_merge($routeParams, $query);

            if ($formatInPath) {
                $params['format'] = $format;
            } else {
                unset($params['format']);
                $params['format'] = $format;
            }

            $availableFormats[$format] = [
                'type' => 'url',
                'url' => route($exportRoute, $params),
            ];
        }
    } else {
        foreach (['csv' => $csvUrl, 'excel' => $excelUrl, 'pdf' => $pdfUrl] as $format => $url) {
            if (filled($url)) {
                $availableFormats[$format] = ['type' => 'url', 'url' => $url];
            }
        }

        if ($postAction) {
            foreach ($formats as $format) {
                $availableFormats[$format] = ['type' => 'post', 'action' => $postAction];
            }
        }
    }

    $disabledTitle ??= __('You do not have permission to export');
?>

<?php if(! $canExport): ?>
    <button
        type="button"
        class="erp-toolbar-export-btn erp-btn-secondary py-2 text-sm opacity-60"
        disabled
        title="<?php echo e($disabledTitle); ?>"
    >
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
    </button>
<?php elseif($availableFormats === []): ?>
    <?php echo e($slot ?? ''); ?>

<?php else: ?>
    <div class="relative" x-data="erpExportDropdown()" @click.outside="exportOpen = false">
        <button
            type="button"
            class="erp-toolbar-export-btn erp-btn-secondary py-2 text-sm"
            :disabled="exporting"
            @click.stop="!exporting && (exportOpen = !exportOpen)"
            title="<?php echo e(__('Export')); ?>"
        >
            <span x-show="!exporting" class="inline-flex items-center gap-2">
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
            <span x-show="exporting" x-cloak class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="erp-toolbar-export-btn__label hidden sm:inline"><?php echo e(__('Exporting…')); ?></span>
            </span>
        </button>
        <div
            x-show="exportOpen && !exporting"
            x-cloak
            class="absolute end-0 z-20 mt-1 min-w-[10rem] rounded-lg border border-erp-border bg-white py-1 shadow-lg"
        >
            <?php $__currentLoopData = $formats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(! isset($availableFormats[$format])) continue; ?>
                <?php $config = $availableFormats[$format]; ?>
                <?php if($config['type'] === 'url'): ?>
                    <button
                        type="button"
                        class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"
                        @click.prevent="downloadUrl(<?php echo \Illuminate\Support\Js::from($config['url'])->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($formatLabels[$format])->toHtml() ?>)"
                    ><?php echo e($formatLabels[$format]); ?></button>
                <?php else: ?>
                    <button
                        type="button"
                        class="flex w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-erp-page"
                        @click.prevent="submitPost(<?php echo \Illuminate\Support\Js::from($config['action'])->toHtml() ?>, <?php echo \Illuminate\Support\Js::from(array_merge(['_token' => csrf_token(), 'format' => $format], $postFields))->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($formatLabels[$format])->toHtml() ?>)"
                    ><?php echo e($formatLabels[$format]); ?></button>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php echo e($slot ?? ''); ?>

        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\export-dropdown.blade.php ENDPATH**/ ?>