<?php
    $kpis = $workspaceData['kpis'];
    $slaDetail = $workspaceData['sla_detail'];
    $messageTimeline = $workspaceData['message_timeline'];
    $lastChannel = $active->last_channel
        ? (\App\Enums\InboxMessageChannel::tryFrom($active->last_channel)?->label() ?? $active->last_channel)
        : __('In-app');
?>

<section class="shared-inbox__thread">
    <?php echo $__env->make('admin.communications.inbox.workspace.thread-header', compact('lastChannel', 'kpis', 'slaDetail'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="relative min-h-0 flex-1 overflow-hidden">
        <div
            class="shared-inbox__thread-messages"
            id="inbox-messages"
            data-inbox-feed-url="<?php echo e(route('admin.communications.inbox.feed', $active)); ?>"
            data-inbox-feed-fingerprint="<?php echo e(app(\App\Support\Communications\Inbox\InboxChatFeedService::class)->fingerprint($messageTimeline)); ?>"
            x-data
            x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
        >
            <?php echo $__env->make('admin.communications.inbox.workspace.chat-messages', ['events' => $messageTimeline], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <?php echo $__env->make('admin.communications.inbox.workspace.composer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\thread-panel.blade.php ENDPATH**/ ?>