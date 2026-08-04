<div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-600" id="inbox-assign">
    <span><strong><?php echo e(__('Owner')); ?>:</strong> <?php echo e($active->owner?->name ?? __('—')); ?></span>
    <span><strong><?php echo e(__('Assigned')); ?>:</strong> <?php echo e($active->assignee?->name ?? __('Unassigned')); ?></span>
    <?php if($active->assignedDepartment): ?><span><strong><?php echo e(__('Team')); ?>:</strong> <?php echo e($active->assignedDepartment->name); ?></span><?php endif; ?>
    <?php if($watchers->isNotEmpty()): ?>
        <span><strong><?php echo e(__('Watchers')); ?>:</strong> <?php echo e($watchers->pluck('name')->join(', ')); ?></span>
    <?php endif; ?>
</div>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
    <div class="mt-2 flex flex-wrap gap-2">
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.assign', $active)); ?>" class="flex flex-wrap gap-1 items-center" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="assign">
            <select name="assigned_user_id" class="erp-input text-xs">
                <option value=""><?php echo e(__('Assign user…')); ?></option>
                <?php $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php if($active->assigned_user_id == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs"><?php echo e(__('Assign')); ?></button>
        </form>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.assign', $active)); ?>" class="inline" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"><?php echo csrf_field(); ?><input type="hidden" name="action" value="take"><button class="erp-btn erp-btn--secondary erp-btn--xs"><?php echo e(__('Take')); ?></button></form>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.assign', $active)); ?>" class="inline" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"><?php echo csrf_field(); ?><input type="hidden" name="action" value="release"><button class="erp-btn erp-btn--secondary erp-btn--xs"><?php echo e(__('Release')); ?></button></form>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('escalate', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
            <form method="POST" action="<?php echo e(route('admin.communications.inbox.assign', $active)); ?>" class="inline" id="inbox-escalate" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"><?php echo csrf_field(); ?><input type="hidden" name="action" value="escalate"><button class="erp-btn erp-btn--secondary erp-btn--xs"><?php echo e(__('Escalate')); ?></button></form>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.assign', $active)); ?>" class="flex gap-1" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php echo csrf_field(); ?><input type="hidden" name="action" value="assign_department">
            <select name="assigned_department_id" class="erp-input text-xs">
                <option value=""><?php echo e(__('Department…')); ?></option>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept->id); ?>" <?php if($active->assigned_department_id == $dept->id): echo 'selected'; endif; ?>><?php echo e($dept->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs"><?php echo e(__('Set')); ?></button>
        </form>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.status', $active)); ?>" class="flex gap-1" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php echo csrf_field(); ?>
            <select name="status" class="erp-input text-xs" onchange="this.form.submit()">
                <?php $__currentLoopData = \App\Enums\InboxConversationStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($st->value); ?>" <?php if($active->status === $st): echo 'selected'; endif; ?>><?php echo e($st->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\assignment-bar.blade.php ENDPATH**/ ?>