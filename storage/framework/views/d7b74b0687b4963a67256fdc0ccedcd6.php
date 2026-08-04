<turbo-frame id="erp-main" data-turbo-action="advance" class="flex flex-1 flex-col">
    <span
        id="erp-route-meta"
        class="sr-only"
        data-route="<?php echo e(Route::currentRouteName()); ?>"
        data-title="<?php echo e($title); ?>"
        data-app-name="<?php echo e(config('app.name')); ?>"
        aria-hidden="true"
    ></span>
    <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <?php echo $__env->make('admin.partials.breadcrumbs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(isset($header)): ?>
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <?php echo e($header); ?>

            </div>
        <?php endif; ?>

        <?php echo e($slot); ?>

    </main>
</turbo-frame>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\layouts\admin-frame.blade.php ENDPATH**/ ?>