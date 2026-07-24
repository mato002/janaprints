<?php
    $operatorMode = (bool) ($operatorMode ?? false);
    $greeting = $greeting ?? ['title' => __('Designer Desk'), 'facts' => []];
    $filters = $filters ?? [];
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $operatorMode ? __('Designer Desk') : __('Artwork Desk'),'breadcrumbs' => $operatorMode
        ? [['label' => __('Designer Desk')]]
        : [
            ['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')],
            ['label' => __('Designer Desk')],
        ],'compactPage' => true] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="designer-desk-shell designer-desk-command"
        x-data="designerDesk(<?php echo \Illuminate\Support\Js::from([
            'panelBase' => url('admin/artwork/desk/requests'),
            'initialRequestKey' => request('request'),
            'autoSelectFirst' => collect($rows)->isNotEmpty(),
            'firstKey' => data_get(collect($rows)->first(), 'key'),
        ])->toHtml() ?>)"
        x-cloak
    >
        <?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        
        <section class="mb-3 flex flex-wrap items-start justify-between gap-3 rounded-xl border border-erp-border bg-white px-4 py-3 shadow-sm">
            <div class="min-w-0">
                <p class="text-base font-semibold text-erp-primary"><?php echo e($greeting['title']); ?></p>
                <p class="mt-0.5 text-xs text-slate-600"><?php echo e(implode(' · ', $greeting['facts'] ?? [])); ?></p>
            </div>
            <?php if (! ($operatorMode)): ?>
                <a href="<?php echo e(route('admin.artwork.dashboard')); ?>" class="erp-btn-secondary shrink-0 text-xs" data-turbo-frame="erp-main"><?php echo e(__('Full dashboard')); ?></a>
            <?php endif; ?>
        </section>

        
        <?php echo $__env->make('admin.artwork.desk.partials.summary-strip', ['summary' => $summary], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <?php echo $__env->make('admin.artwork.desk.partials.queue-filters', ['filters' => $filters], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div class="designer-desk-split grid gap-3 lg:grid-cols-12 lg:items-start">
            <div class="lg:col-span-5 xl:col-span-4">
                <?php echo $__env->make('admin.artwork.desk.partials.queue-cards', [
                    'rows' => $rows,
                    'requests' => $requests,
                    'has_assignments' => $has_assignments,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="lg:col-span-7 xl:col-span-8">
                <?php echo $__env->make('admin.artwork.desk.partials.workspace', ['operatorMode' => $operatorMode], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.artwork.desk.partials.idle-panel', [
                    'today_activity' => $today_activity,
                    'has_assignments' => $has_assignments,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/desk/index.blade.php ENDPATH**/ ?>