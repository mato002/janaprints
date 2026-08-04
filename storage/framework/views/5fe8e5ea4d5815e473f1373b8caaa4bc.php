<?php if($context && ! empty($context['linked_records'])): ?>
    <?php echo $__env->make('admin.communications.inbox.partials.linked-records', [
        'records' => $context['linked_records'],
        'collapsed' => true,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <p class="text-sm text-slate-500"><?php echo e(__('No linked ERP records.')); ?></p>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\tab-records.blade.php ENDPATH**/ ?>