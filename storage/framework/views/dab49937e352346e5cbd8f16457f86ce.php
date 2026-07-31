<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Command Center'),'breadcrumbs' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="exec-dashboard exec-dashboard--v2 exec-dashboard--density-pilot">
        <header class="exec-dashboard__header">
            <div>
                <h1 class="exec-dashboard__title"><?php echo e(__('Executive Command Center')); ?></h1>
                <p class="exec-dashboard__context">
                    <?php echo e($dashboard['context']['company']); ?> · <?php echo e($dashboard['context']['branch']); ?> · <?php echo e($dashboard['context']['role']); ?>

                </p>
            </div>
            <span class="exec-live-badge">
                <span class="exec-live-badge__dot" aria-hidden="true"></span>
                <?php echo e(__('Live operations')); ?>

            </span>
        </header>

        <?php echo $__env->make('admin.dashboard.partials.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.dashboard.partials.operator-desks', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.dashboard.partials.quote-requests-alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.dashboard.partials.health-strip', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.dashboard.partials.communication-health', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.dashboard.partials.integration-health', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.dashboard.partials.pipeline', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="exec-dashboard__main grid grid-cols-1 gap-2 md:gap-3 xl:grid-cols-12">
            <div class="exec-dashboard__primary space-y-2 md:space-y-3 xl:col-span-8">
                <?php echo $__env->make('admin.dashboard.partials.attention-center', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.dashboard.partials.today-ops', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.dashboard.partials.charts-grid', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="grid grid-cols-1 gap-2 md:gap-3 lg:grid-cols-2">
                    <?php echo $__env->make('admin.dashboard.partials.branch-performance', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php echo $__env->make('admin.dashboard.partials.top-customers', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <?php echo $__env->make('admin.dashboard.partials.intelligence', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <aside class="exec-dashboard__rail xl:col-span-4">
                <?php echo $__env->make('admin.dashboard.partials.activity-feed', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </aside>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>