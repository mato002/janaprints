<?php echo $__env->make('admin.production.specifications.partials.read-only-display', ['specification' => $tabData['specification'] ?? ['has_specification' => false]], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if(! empty($tabData['edit_url'])): ?>
    <div class="mt-4">
        <a href="<?php echo e($tabData['edit_url']); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('Edit specification')); ?></a>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\specification.blade.php ENDPATH**/ ?>