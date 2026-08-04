<p class="mb-3 text-[11px] text-slate-500"><?php echo e(__('Assignment, SLA, and tags — kept here so the chat stays message-first.')); ?></p>

<div class="grid grid-cols-2 gap-2 rounded-lg border border-erp-border bg-white p-2 text-[11px] text-slate-600">
    <div><span class="text-slate-500"><?php echo e(__('Messages')); ?></span><br><span class="font-medium"><?php echo e($kpis['total_messages']); ?></span></div>
    <div><span class="text-slate-500"><?php echo e(__('Age')); ?></span><br><span class="font-medium"><?php echo e($kpis['conversation_age_label']); ?></span></div>
    <div><span class="text-slate-500"><?php echo e(__('1st response')); ?></span><br><?php echo e($kpis['first_response_minutes'] ?? '—'); ?>m</div>
    <div><span class="text-slate-500"><?php echo e(__('Last response')); ?></span><br><?php echo e($kpis['last_response_minutes'] ?? '—'); ?>m</div>
    <div><span class="text-slate-500"><?php echo e(__('SLA 1st')); ?></span><br><span class="<?php echo e($slaDetail['first_response']->badgeClass()); ?> rounded px-1"><?php echo e($slaDetail['first_response']->label()); ?></span></div>
    <div><span class="text-slate-500"><?php echo e(__('SLA follow')); ?></span><br><span class="<?php echo e($slaDetail['follow_up']->badgeClass()); ?> rounded px-1"><?php echo e($slaDetail['follow_up']->label()); ?></span></div>
    <div><span class="text-slate-500"><?php echo e(__('SLA resolve')); ?></span><br><span class="<?php echo e($slaDetail['resolution']->badgeClass()); ?> rounded px-1"><?php echo e($slaDetail['resolution']->label()); ?></span></div>
    <div><span class="text-slate-500"><?php echo e(__('SLA')); ?></span><br><span class="<?php echo e($kpis['sla_status']->badgeClass()); ?> rounded px-1 font-semibold"><?php echo e($kpis['sla_status']->label()); ?></span></div>
</div>

<div class="mt-3 rounded-lg border border-erp-border bg-white p-2">
    <?php echo $__env->make('admin.communications.inbox.partials.assignment-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.assign', $active)); ?>" class="mt-2 flex gap-1" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add_watcher">
            <select name="watcher_user_id" class="erp-input flex-1 text-xs">
                <option value=""><?php echo e(__('Add watcher…')); ?></option>
                <?php $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs"><?php echo e(__('Add')); ?></button>
        </form>
    <?php endif; ?>
</div>

<div class="mt-3">
    <?php echo $__env->make('admin.communications.inbox.workspace.tags-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\tab-manage.blade.php ENDPATH**/ ?>