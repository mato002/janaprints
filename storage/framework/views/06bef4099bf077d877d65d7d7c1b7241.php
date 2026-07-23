<?php if($row['job_360_url']): ?>
    <a href="<?php echo e($row['job_360_url']); ?>" class="erp-btn-primary text-xs py-2 px-3" data-turbo-frame="erp-main"><?php echo e(__('Open Job 360')); ?></a>
<?php endif; ?>

<?php if(! empty($row['customer_360_url'])): ?>
    <a href="<?php echo e($row['customer_360_url']); ?>" class="erp-btn-secondary text-xs py-2 px-3" data-turbo-frame="erp-main"><?php echo e(__('Customer 360')); ?></a>
<?php endif; ?>

<?php if(! empty($row['print_url'])): ?>
    <a href="<?php echo e($row['print_url']); ?>" class="erp-btn-secondary text-xs py-2 px-3" target="_blank" rel="noopener"><?php echo e(__('Print')); ?></a>
<?php endif; ?>

<?php if(! empty($row['quick_actions'])): ?>
    <details class="relative inline-block text-left">
        <summary class="erp-btn-secondary cursor-pointer list-none text-xs py-2 px-3 [&::-webkit-details-marker]:hidden"><?php echo e(__('More')); ?></summary>
        <div class="absolute right-0 z-10 mt-1 min-w-[10rem] rounded-md border border-erp-border bg-white py-1 shadow-lg">
            <?php $__currentLoopData = $row['quick_actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($action['type'] ?? 'link') === 'post'): ?>
                    <form method="POST" action="<?php echo e($action['url']); ?>" class="block">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"><?php echo e($action['label']); ?></button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e($action['url']); ?>" class="block px-3 py-2 text-xs text-slate-700 hover:bg-slate-50" data-turbo-frame="erp-main"><?php echo e($action['label']); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </details>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/queue/partials/row-actions.blade.php ENDPATH**/ ?>