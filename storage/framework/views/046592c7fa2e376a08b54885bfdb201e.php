<?php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Procurement\ProcurementDeskViews;

    $activeProcurementView = ProcurementDeskViews::normalize($activeProcurementView ?? request('view'));
    $isPanel = ProcurementDeskViews::isPanelView($activeProcurementView);
    $deskTitle = $isPanel ? ($registerTitle ?? __('Buy Desk')) : __('Buy Desk');
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $deskTitle,'breadcrumbs' => [
        ['label' => __('Supply Chain'), 'url' => $fullSupplyChainDeskUrl],
        ['label' => __('Buy Desk')],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="buy-desk-command store-desk-command">
        <?php if (! (WorkspaceEmbed::inWorkspaceContext())): ?>
            <?php echo $__env->make('admin.procurement.partials.desk-mode-nav', ['activeProcurementView' => $activeProcurementView], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

        <?php if($isPanel): ?>
            <?php echo $__env->make('admin.procurement.desk.partials.register-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('admin.procurement.desk.partials.summary-strip', ['workQueue' => $workQueue], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php echo $__env->make('admin.procurement.desk.partials.pipeline', ['pipelineStages' => $pipelineStages], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    <?php echo $__env->make('admin.procurement.desk.partials.needs-attention', [
                        'needsAttention' => $workQueue['needs_attention'] ?? [],
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="lg:col-span-2">
                    <?php echo $__env->make('admin.procurement.desk.partials.fast-actions', ['fastActions' => $fastActions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-2">
                <?php echo $__env->make('admin.procurement.desk.partials.work-queue', ['queueItems' => $queueItems], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.procurement.desk.partials.receiving-pipeline', [
                    'receivingPipeline' => $receivingPipeline,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\procurement\desk\index.blade.php ENDPATH**/ ?>