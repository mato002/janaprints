<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('notes', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
    <form method="POST" action="<?php echo e(route('admin.communications.inbox.notes.store', $active)); ?>" class="mb-4 space-y-2 rounded-lg border border-amber-200 bg-amber-50/50 p-3" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
        <?php echo csrf_field(); ?>
        <?php if($channelFilter): ?><input type="hidden" name="channel" value="<?php echo e($channelFilter); ?>"><?php endif; ?>
        <p class="text-[10px] font-semibold uppercase text-amber-800"><?php echo e(__('Staff only — customer never sees this')); ?></p>
        <textarea name="body" rows="3" class="erp-input w-full text-sm bg-white" placeholder="<?php echo e(__('@name mentions · #urgent #artwork tags')); ?>" required></textarea>
        <input type="text" name="tags" class="erp-input w-full text-xs" placeholder="<?php echo e(__('Tags (comma separated)')); ?>">
        <button type="submit" class="erp-btn erp-btn--secondary erp-btn--sm w-full"><?php echo e(__('Add note')); ?></button>
    </form>
<?php endif; ?>

<ul class="space-y-2">
    <?php
        $sortedNotes = $active->notes->sortByDesc(fn ($n) => in_array('pinned', $n->tags ?? [], true) || in_array('important', $n->tags ?? [], true));
    ?>
    <?php $__empty_1 = true; $__currentLoopData = $sortedNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $isPinned = ! empty(array_intersect($note->tags ?? [], ['pinned', 'important']));
        ?>
        <li class="rounded-lg border p-3 text-sm <?php echo e($isPinned ? 'border-amber-400 bg-amber-50' : 'border-erp-border bg-white'); ?>">
            <p class="flex items-center gap-2 text-[10px] font-semibold text-slate-600">
                <?php if($isPinned): ?><span class="rounded bg-amber-200 px-1 text-amber-900"><?php echo e(__('Pinned')); ?></span><?php endif; ?>
                <?php echo e($note->author?->name); ?> · <?php echo e($note->created_at->format('d M Y H:i')); ?>

            </p>
            <p class="mt-1 whitespace-pre-wrap text-slate-800"><?php echo e($note->body); ?></p>
            <?php if(! empty($note->tags)): ?>
                <p class="mt-1 flex flex-wrap gap-1">
                    <?php $__currentLoopData = $note->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="rounded bg-slate-100 px-1.5 text-[10px]">#<?php echo e($tag); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </p>
            <?php endif; ?>
            <?php if(! empty($note->mentioned_user_ids)): ?>
                <p class="mt-1 text-[10px] text-erp-accent"><?php echo e(__('Mentioned')); ?>: <?php $__currentLoopData = $note->mentioned_user_ids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>@__raw_block_2__{{ $u?->name ?? $uid }}<?php if(! $loop->last): ?>, <?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></p>
            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="text-sm text-slate-500"><?php echo e(__('No internal notes yet.')); ?></li>
    <?php endif; ?>
</ul>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\tab-notes.blade.php ENDPATH**/ ?>