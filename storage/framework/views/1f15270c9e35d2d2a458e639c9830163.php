<div class="border-t border-erp-border bg-white p-3 space-y-2">
    <?php if($context && ! empty($context['quick_actions'])): ?>
        <div class="flex flex-wrap gap-1 pb-2 border-b border-erp-border/60">
            <?php $__currentLoopData = $context['quick_actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($action['route'] && (! $action['permission'] || auth()->user()->can($action['permission']))): ?>
                    <a href="<?php echo e(route($action['route'], $active->customer_id ? ['customer_id' => $active->customer_id] : [])); ?>"
                       class="erp-btn erp-btn--secondary erp-btn--xs" data-turbo-frame="erp-main"><?php echo e($action['label']); ?></a>
                <?php elseif(! empty($action['anchor'])): ?>
                    <a href="#<?php echo e($action['anchor']); ?>" class="erp-btn erp-btn--secondary erp-btn--xs"><?php echo e($action['label']); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reply', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.reply', $active)); ?>" class="flex gap-2">
            <?php echo csrf_field(); ?>
            <select name="channel" class="erp-input w-28 text-xs shrink-0">
                <?php $__currentLoopData = \App\Enums\InboxMessageChannel::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($ch->value); ?>"><?php echo e($ch->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <textarea name="body" rows="2" class="erp-input flex-1 text-sm" placeholder="<?php echo e(__('Reply…')); ?>" required></textarea>
            <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm shrink-0"><?php echo e(__('Send')); ?></button>
        </form>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('notes', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.notes.store', $active)); ?>" class="flex gap-2" id="inbox-note">
            <?php echo csrf_field(); ?>
            <textarea name="body" rows="2" class="erp-input flex-1 text-sm bg-amber-50"
                      placeholder="<?php echo e(__('Internal note — @name mentions, #tags')); ?>" required></textarea>
            <input type="text" name="tags" class="erp-input w-24 text-xs shrink-0" placeholder="<?php echo e(__('Tags')); ?>">
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--sm shrink-0"><?php echo e(__('Note')); ?></button>
        </form>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\composer.blade.php ENDPATH**/ ?>