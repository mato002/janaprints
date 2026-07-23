<article class="so-360__card">
    <h2 class="so-360__card-title"><?php echo e(__('Notes')); ?></h2>

    <?php $__empty_1 = true; $__currentLoopData = $salesOrder->orderNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="border-b border-slate-100 py-2 text-sm last:border-0">
            <p class="font-medium text-slate-800"><?php echo e($note->user?->name ?? __('System')); ?></p>
            <p class="text-slate-600"><?php echo e($note->note); ?></p>
            <?php if($note->created_at): ?>
                <p class="mt-0.5 text-xs text-slate-400"><?php echo e($note->created_at->format('M j, Y H:i')); ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-sm text-slate-500"><?php echo e(__('No notes yet.')); ?></p>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $salesOrder)): ?>
        <form method="POST" action="<?php echo e(route('admin.sales-orders.notes.store', $salesOrder)); ?>" class="mt-4 space-y-2">
            <?php echo csrf_field(); ?>
            <textarea name="note" class="erp-input w-full" rows="3" required placeholder="<?php echo e(__('Add an internal note…')); ?>"></textarea>
            <button class="erp-btn-secondary"><?php echo e(__('Add note')); ?></button>
        </form>
    <?php endif; ?>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\workspace\tabs\notes.blade.php ENDPATH**/ ?>