<div class="shared-inbox__toolbar">
    <div class="shared-inbox__toolbar-title">
        <h1 class="text-base font-semibold text-slate-900"><?php echo e(__('Shared Inbox')); ?></h1>
        <p class="text-xs text-slate-500"><?php echo e(__('Customer conversations across channels')); ?></p>
    </div>
    <div class="shared-inbox__toolbar-actions" role="toolbar" aria-label="<?php echo e(__('Inbox actions')); ?>">
        <button
            type="button"
            class="shared-inbox__icon-btn"
            title="<?php echo e(__('New conversation')); ?>"
            aria-label="<?php echo e(__('New conversation')); ?>"
            @click="newConvoOpen = !newConvoOpen"
            :class="newConvoOpen && 'shared-inbox__icon-btn--active'"
        >
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
        </button>
        <a
            href="<?php echo e($inboxEmbedUrl(route('admin.communications.inbox.index', request()->query()))); ?>"
            class="shared-inbox__icon-btn"
            title="<?php echo e(__('Refresh')); ?>"
            aria-label="<?php echo e(__('Refresh')); ?>"
            data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
        >
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
            </svg>
        </a>
        <button
            type="button"
            class="shared-inbox__icon-btn"
            title="<?php echo e(__('Export view')); ?>"
            aria-label="<?php echo e(__('Export view')); ?>"
            onclick="window.print()"
        >
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3"/>
            </svg>
        </button>
        <a
            href="<?php echo e(route('admin.communications.inbox.team')); ?>"
            class="shared-inbox__icon-btn"
            title="<?php echo e(__('Assignment queue')); ?>"
            aria-label="<?php echo e(__('Assignment queue')); ?>"
            data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
        >
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M18 18.72a9.09 9.09 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-5.058-2.772m0 0a5.995 5.995 0 0110.116 0"/>
            </svg>
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('executive', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
            <a href="<?php echo e($inboxEmbedUrl(route('admin.communications.inbox.executive'))); ?>" class="shared-inbox__toolbar-link" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"><?php echo e(__('CEO view')); ?></a>
        <?php endif; ?>
        <a href="<?php echo e($inboxEmbedUrl(route('admin.communications.inbox.team'))); ?>" class="shared-inbox__toolbar-link" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"><?php echo e(__('Team')); ?></a>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\top-action-bar.blade.php ENDPATH**/ ?>