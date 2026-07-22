<?php
    $operatorMode = (bool) ($operatorMode ?? false);
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Store Desk'),'breadcrumbs' => $operatorMode
        ? [['label' => __('Store Desk')]]
        : [
            ['label' => __('Supply Chain'), 'url' => $fullSupplyChainDeskUrl],
            ['label' => __('Store Desk')],
        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="store-desk-shell"
        x-data="storeDeskLookup(<?php echo \Illuminate\Support\Js::from([
            'searchUrl' => $searchUrl,
        ])->toHtml() ?>)"
    >
        <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-erp-primary"><?php echo e(__('Store desk')); ?></p>
                <p class="text-xs text-slate-600"><?php echo e(__('Receive, issue, transfer, adjust, and verify stock from one transaction hub.')); ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if(! $operatorMode): ?>
                    <a href="<?php echo e($fullSupplyChainDeskUrl); ?>" class="erp-btn-secondary text-xs" data-turbo-frame="_top"><?php echo e(__('Full Supply Chain desk')); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <?php if(session('status')): ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc pl-4">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php echo $__env->make('admin.store.desk.partials.summary-strip', ['workQueue' => $workQueue], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.store.desk.partials.work-queue', ['workQueue' => $workQueue], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.store.desk.partials.fast-actions', ['fastActions' => $fastActions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.store.desk.partials.item-lookup', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.store.desk.partials.warehouse-snapshot', ['warehouseSnapshot' => $warehouseSnapshot], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.store.desk.partials.pipelines', [
            'receivingPipeline' => $receivingPipeline,
            'issuePipeline' => $issuePipeline,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.store.desk.partials.movement-feed', ['movementFeed' => $movementFeed], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.store.desk.partials.low-stock', [
            'lowStockItems' => $lowStockItems,
            'reorderAlertsUrl' => $reorderAlertsUrl,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.store.desk.partials.reorder-suggestions', [
            'reorderRecommendations' => $reorderRecommendations,
            'reorderAlertsUrl' => $reorderAlertsUrl,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/store/desk/index.blade.php ENDPATH**/ ?>