<?php if(! empty($summary)): ?>
    <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 rounded-lg border border-erp-border bg-white px-4 py-3 text-sm text-slate-600">
        <span><span class="font-medium text-slate-800"><?php echo e(__('Visible jobs')); ?>:</span> <?php echo e($summary['total_visible'] ?? 0); ?></span>
        <span><span class="font-medium text-slate-800"><?php echo e(__('Waiting')); ?>:</span> <?php echo e($summary['waiting'] ?? 0); ?></span>
        <span><span class="font-medium text-slate-800"><?php echo e(__('Running')); ?>:</span> <?php echo e($summary['running'] ?? 0); ?></span>
        <span><span class="font-medium text-slate-800"><?php echo e(__('Overdue')); ?>:</span> <?php echo e($summary['overdue'] ?? 0); ?></span>
        <span><span class="font-medium text-slate-800"><?php echo e(__('Completed today')); ?>:</span> <?php echo e($summary['completed_today'] ?? 0); ?></span>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\summary.blade.php ENDPATH**/ ?>