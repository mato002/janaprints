<?php
    $operatorMode = (bool) ($operatorMode ?? false);
    $health = $workQueue['health'] ?? ['percent' => 100, 'label' => __('Healthy'), 'tone' => 'emerald', 'detail' => ''];
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
        class="store-desk-command"
        x-data="storeDeskLookup(<?php echo \Illuminate\Support\Js::from([
            'searchUrl' => $searchUrl,
        ])->toHtml() ?>)"
    >
        <?php if (! (\App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext())): ?>
            <?php echo $__env->make('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => \App\Support\Inventory\StoreDeskViews::DESK], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>

        <?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
                <ul class="list-disc pl-4">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <?php echo $__env->make('admin.store.desk.partials.item-lookup', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <?php echo $__env->make('admin.store.desk.partials.summary-strip', ['workQueue' => $workQueue], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <?php echo $__env->make('admin.store.desk.partials.needs-attention', [
                    'needsAttention' => $workQueue['needs_attention'] ?? [],
                    'lowStockItems' => $lowStockItems,
                    'reorderAlertsUrl' => $reorderAlertsUrl,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div class="lg:col-span-2">
                <?php echo $__env->make('admin.store.desk.partials.fast-actions', ['fastActions' => $fastActions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        
        <?php echo $__env->make('admin.store.desk.partials.warehouse-snapshot', ['warehouseSnapshot' => $warehouseSnapshot], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-2">
            <?php echo $__env->make('admin.store.desk.partials.movement-feed', ['movementFeed' => $movementFeed], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('admin.store.desk.partials.pipelines', [
                'receivingPipeline' => $receivingPipeline,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/store/desk/index.blade.php ENDPATH**/ ?>