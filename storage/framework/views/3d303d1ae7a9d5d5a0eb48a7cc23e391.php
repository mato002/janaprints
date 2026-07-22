<div class="mb-2 flex flex-wrap items-center gap-1">
    <span class="text-[10px] font-semibold uppercase text-slate-500 mr-1"><?php echo e(__('Channel')); ?></span>
    <?php
        $baseQuery = array_merge(request()->query(), ['conversation' => $active->id]);
    ?>
    <a href="<?php echo e($inboxEmbedUrl(route('admin.communications.inbox.index', collect($baseQuery)->except('channel')->all()))); ?>"
       data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
       class="rounded-full px-2 py-0.5 text-[10px] <?php echo e(empty($channelFilter) ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-600'); ?>"><?php echo e(__('All')); ?></a>
    <?php $__currentLoopData = \App\Enums\InboxMessageChannel::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e($inboxEmbedUrl(route('admin.communications.inbox.index', array_merge($baseQuery, ['channel' => $ch->value])))); ?>"
           data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
           class="rounded-full px-2 py-0.5 text-[10px] <?php echo e(($channelFilter ?? '') === $ch->value ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-600'); ?>">
            <?php echo e($ch->label()); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e($inboxEmbedUrl(route('admin.communications.inbox.index', array_merge($baseQuery, ['channel' => 'note'])))); ?>"
       data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
       class="rounded-full px-2 py-0.5 text-[10px] <?php echo e(($channelFilter ?? '') === 'note' ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-600'); ?>"><?php echo e(__('Notes')); ?></a>
    <a href="<?php echo e($inboxEmbedUrl(route('admin.communications.inbox.index', array_merge($baseQuery, ['channel' => 'erp'])))); ?>"
       data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
       class="rounded-full px-2 py-0.5 text-[10px] <?php echo e(($channelFilter ?? '') === 'erp' ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-600'); ?>"><?php echo e(__('ERP')); ?></a>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\channel-filter.blade.php ENDPATH**/ ?>