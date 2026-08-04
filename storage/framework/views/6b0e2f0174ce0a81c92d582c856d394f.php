<div class="shrink-0 border-b border-erp-border bg-white/95 px-3 py-2 text-[10px] text-slate-600">
    <div class="grid grid-cols-3 gap-x-2 gap-y-1 sm:grid-cols-5 lg:grid-cols-9">
        <div><span class="font-semibold text-slate-500"><?php echo e(__('Messages')); ?></span><br><?php echo e($kpis['total_messages']); ?></div>
        <div><span class="font-semibold text-slate-500"><?php echo e(__('Age')); ?></span><br><?php echo e($kpis['conversation_age_label']); ?></div>
        <div><span class="font-semibold text-slate-500"><?php echo e(__('1st reply')); ?></span><br><?php echo e($kpis['first_response_minutes'] ?? '—'); ?>m</div>
        <div><span class="font-semibold text-slate-500"><?php echo e(__('Last reply')); ?></span><br><?php echo e($kpis['last_response_minutes'] ?? '—'); ?>m</div>
        <div><span class="font-semibold text-slate-500"><?php echo e(__('Assigned')); ?></span><br class="truncate"><?php echo e($kpis['assigned_user']); ?></div>
        <div><span class="font-semibold text-slate-500"><?php echo e(__('Unread')); ?></span><br><?php echo e($kpis['unread_count']); ?></div>
        <div><span class="font-semibold text-slate-500"><?php echo e(__('SLA 1st')); ?></span><br><span class="<?php echo e($slaDetail['first_response']->badgeClass()); ?> rounded px-1"><?php echo e($slaDetail['first_response']->label()); ?></span></div>
        <div><span class="font-semibold text-slate-500"><?php echo e(__('SLA follow')); ?></span><br><span class="<?php echo e($slaDetail['follow_up']->badgeClass()); ?> rounded px-1"><?php echo e($slaDetail['follow_up']->label()); ?></span></div>
        <div><span class="font-semibold text-slate-500"><?php echo e(__('SLA resolve')); ?></span><br><span class="<?php echo e($slaDetail['resolution']->badgeClass()); ?> rounded px-1"><?php echo e($slaDetail['resolution']->label()); ?></span></div>
    </div>
    <details class="mt-1 lg:hidden">
        <summary class="cursor-pointer text-erp-accent"><?php echo e(__('Assignment & watchers')); ?></summary>
        <div class="mt-1"><?php echo $__env->make('admin.communications.inbox.partials.assignment-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    </details>
    <div class="mt-1 hidden lg:block"><?php echo $__env->make('admin.communications.inbox.partials.assignment-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\kpis-bar.blade.php ENDPATH**/ ?>