<p class="mb-3 text-xs text-slate-500"><?php echo e(__('Full activity: messages, ERP events, system changes.')); ?></p>
<?php echo $__env->make('admin.communications.inbox.workspace.channel-filter', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="space-y-2 max-h-[calc(100vh-16rem)] overflow-y-auto">
    <?php echo $__env->make('admin.communications.inbox.workspace.timeline-feed', ['events' => $events], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\tab-timeline.blade.php ENDPATH**/ ?>