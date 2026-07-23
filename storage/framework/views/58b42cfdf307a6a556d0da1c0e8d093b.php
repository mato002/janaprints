<details class="exec-intelligence">
    <summary class="exec-intelligence__summary">
        <span><?php echo e(__('Operations intelligence')); ?></span>
        <span class="exec-intelligence__hint"><?php echo e(__('CRM, insights, quick actions, inventory & finance')); ?></span>
    </summary>
    <div class="exec-intelligence__body space-y-2 md:space-y-3">
        <div class="grid grid-cols-1 gap-2 md:gap-3 lg:grid-cols-2">
            <?php echo $__env->make('admin.dashboard.partials.crm-hr-pulse', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('admin.dashboard.partials.smart-insights', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <?php echo $__env->make('admin.dashboard.partials.quick-actions', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="grid grid-cols-1 gap-2 md:gap-3 lg:grid-cols-2">
            <?php echo $__env->make('admin.dashboard.partials.inventory-health', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('admin.dashboard.partials.finance-snapshot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <?php echo $__env->make('admin.dashboard.partials.production-performance', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</details>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\intelligence.blade.php ENDPATH**/ ?>