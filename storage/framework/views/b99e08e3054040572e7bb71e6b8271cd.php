<turbo-frame id="module-workspace-content" class="flex min-h-0 flex-1 flex-col overflow-hidden">
    <div class="module-workspace-embedded flex min-h-0 w-full min-w-0 flex-1 flex-col overflow-x-hidden overflow-y-auto">
        <?php echo $__env->make('admin.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if(isset($header)): ?>
            <div class="mb-2"><?php echo e($header); ?></div>
        <?php endif; ?>
        <?php echo e($slot); ?>

    </div>
</turbo-frame>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\layouts\admin-embedded.blade.php ENDPATH**/ ?>