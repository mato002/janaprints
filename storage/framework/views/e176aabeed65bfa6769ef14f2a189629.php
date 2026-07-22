<div
    x-data="{
        drawerOpen: false,
        loading: false,
        detail: null,
        async openDrawer(messageId) {
            this.drawerOpen = true;
            this.loading = true;
            this.detail = null;
            try {
                const response = await fetch(`<?php echo e(url('admin/communications/email/messages')); ?>/${messageId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (response.ok) {
                    const data = await response.json();
                    this.detail = data.message;
                }
            } finally {
                this.loading = false;
            }
        },
        closeDrawer() {
            this.drawerOpen = false;
            this.detail = null;
        },
    }"
>
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead>
                <tr>
                    <th><?php echo e(__('Subject')); ?></th>
                    <th><?php echo e(__('To')); ?></th>
                    <th><?php echo e(__('Sender')); ?></th>
                    <th><?php echo e(__('Status')); ?></th>
                    <?php if(($viewMode ?? '') === 'inbox'): ?>
                        <th><?php echo e(__('Failure')); ?></th>
                        <th><?php echo e(__('Retries')); ?></th>
                        <th><?php echo e(__('Last attempt')); ?></th>
                    <?php elseif(($viewMode ?? '') === 'queued'): ?>
                        <th><?php echo e(__('Queued')); ?></th>
                    <?php else: ?>
                        <th><?php echo e(__('Sent')); ?></th>
                    <?php endif; ?>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $metadata = $message->provider_response['metadata'] ?? [];
                        $retryCount = (int) ($message->provider_response['retry_count'] ?? 0);
                        $lastAttempt = $message->provider_response['last_attempt_at'] ?? null;
                    ?>
                    <tr>
                        <td><?php echo e(Str::limit($message->subject, 40)); ?></td>
                        <td class="text-xs"><?php echo e(collect($message->to_emails)->pluck('email')->join(', ')); ?></td>
                        <td class="text-xs"><?php echo e($message->account?->from_email ?? '—'); ?></td>
                        <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase <?php echo e($message->status->badgeClass()); ?>"><?php echo e($message->status->label()); ?></span></td>
                        <?php if(($viewMode ?? '') === 'inbox'): ?>
                            <td class="max-w-[12rem] truncate text-xs text-red-600" title="<?php echo e($message->failure_reason); ?>"><?php echo e(Str::limit($message->failure_reason, 40) ?: '—'); ?></td>
                            <td class="text-xs"><?php echo e($retryCount); ?></td>
                            <td class="text-xs"><?php echo e($lastAttempt ? \Illuminate\Support\Carbon::parse($lastAttempt)->format('d M Y H:i') : ($message->failed_at?->format('d M Y H:i') ?? '—')); ?></td>
                        <?php elseif(($viewMode ?? '') === 'queued'): ?>
                            <td class="text-xs"><?php echo e($message->queued_at?->format('d M Y H:i') ?? '—'); ?></td>
                        <?php else: ?>
                            <td class="text-xs"><?php echo e($message->sent_at?->format('d M Y H:i') ?? '—'); ?></td>
                        <?php endif; ?>
                        <td class="whitespace-nowrap text-right">
                            <button type="button" class="text-erp-accent text-sm" @click="openDrawer(<?php echo e($message->id); ?>)"><?php echo e(__('View')); ?></button>
                            <?php if(($viewMode ?? '') === 'queued'): ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cancel', $message)): ?>
                                    <form method="POST" action="<?php echo e(route('admin.communications.email.messages.cancel', $message)); ?>" class="inline ml-2"><?php echo csrf_field(); ?>
                                        <button type="submit" class="text-sm text-red-600" onclick="return confirm(<?php echo \Illuminate\Support\Js::from(__('Cancel this queued email?'))->toHtml() ?>)"><?php echo e(__('Cancel')); ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php elseif(($viewMode ?? '') === 'inbox'): ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('retry', $message)): ?>
                                    <form method="POST" action="<?php echo e(route('admin.communications.email.messages.retry', $message)); ?>" class="inline ml-2"><?php echo csrf_field(); ?>
                                        <button type="submit" class="text-sm text-erp-accent"><?php echo e(__('Retry')); ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="py-6 text-center text-slate-500"><?php echo e(__('No messages.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if($messages->hasPages()): ?><div class="mt-3"><?php echo e($messages->links()); ?></div><?php endif; ?>
    </div>

    <?php echo $__env->make('admin.communications.email.partials.detail-drawer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\partials\message-table.blade.php ENDPATH**/ ?>