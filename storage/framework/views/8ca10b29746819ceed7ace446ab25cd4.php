<article class="so-360__card">
    <h2 class="so-360__card-title"><?php echo e(__('Attachments')); ?></h2>

    <?php $__empty_1 = true; $__currentLoopData = $salesOrder->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-2 text-sm last:border-0">
            <span><?php echo e($attachment->original_name); ?></span>
            <?php if($attachment->uploader?->name): ?>
                <span class="text-xs text-slate-400"><?php echo e($attachment->uploader->name); ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-sm text-slate-500"><?php echo e(__('No attachments yet.')); ?></p>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $salesOrder)): ?>
        <form method="POST" action="<?php echo e(route('admin.sales-orders.attachments.store', $salesOrder)); ?>" enctype="multipart/form-data" data-turbo-frame="erp-main" class="mt-4 space-y-2">
            <?php echo csrf_field(); ?>
            <input type="file" name="file" class="erp-input w-full" required>
            <button class="erp-btn-secondary"><?php echo e(__('Upload')); ?></button>
        </form>
    <?php endif; ?>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/orders/workspace/tabs/attachments.blade.php ENDPATH**/ ?>