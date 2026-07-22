<section class="space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="ess-card flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold"><?php echo e($document->title); ?></p>
                <p class="text-sm text-erp-muted"><?php echo e($document->category->label()); ?></p>
                <p class="text-xs text-erp-muted"><?php echo e($document->created_at?->format('d M Y')); ?></p>
            </div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ess.documents.download')): ?>
                <a href="<?php echo e(route('ess.documents.download', $document)); ?>" class="ess-btn ess-btn--primary w-full sm:w-auto"><?php echo e(__('Download')); ?></a>
            <?php endif; ?>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="ess-card text-sm text-erp-muted"><?php echo e(__('No documents available.')); ?></div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\tabs\documents.blade.php ENDPATH**/ ?>