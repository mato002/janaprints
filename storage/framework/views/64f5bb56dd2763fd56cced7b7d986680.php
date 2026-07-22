<?php
    $canManage = auth()->user()->can('reply', App\Models\Communications\Inbox\CommunicationConversation::class);
    $canManageFiles = auth()->user()->can('attachments', App\Models\Communications\Inbox\CommunicationConversation::class);
?>

<div
    class="space-y-0.5"
    x-data="{
        lightboxUrl: null,
        openLightbox(url) { this.lightboxUrl = url; },
        closeLightbox() { this.lightboxUrl = null; },
        scrollToChat(id) {
            const el = document.getElementById(id);
            if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); el.classList.add('ring-2', 'ring-erp-accent'); setTimeout(() => el.classList.remove('ring-2', 'ring-erp-accent'), 2000); }
        },
        copyText(text) {
            navigator.clipboard?.writeText(text);
        },
    }"
    @open-chat-item.window="scrollToChat($event.detail)"
    @keydown.escape.window="closeLightbox()"
>
    <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $type = $event['type'] ?? 'message';
            $isOutgoing = ($event['direction'] ?? '') === 'outgoing';
            $isAttachment = $type === 'attachment';
            $isImage = $isAttachment && ! empty($event['is_image']) && ! empty($event['file_url']);
            $domId = $event['dom_id'] ?? ('chat-'.uniqid());
            $caption = trim((string) ($event['caption'] ?? ''));
            $canDelete = $isOutgoing && (
                ($isAttachment && $canManageFiles && ! empty($event['attachment_id']))
                || (! $isAttachment && $canManage && ! empty($event['can_manage']))
            );
        ?>
        <div id="<?php echo e($domId); ?>" class="group mb-3 flex <?php echo e($isOutgoing ? 'justify-end' : 'justify-start'); ?>">
            <div class="relative max-w-[min(70%,420px)]">
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'overflow-hidden',
                    'shared-inbox__msg-out' => $isOutgoing && ! $isImage,
                    'shared-inbox__msg-in' => ! $isOutgoing && ! $isImage,
                    'rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-200/60' => $isImage,
                ]); ?>">
                    <?php if($isAttachment): ?>
                        <?php if($isImage): ?>
                            <button type="button" @click="openLightbox(<?php echo \Illuminate\Support\Js::from($event['file_url'])->toHtml() ?>)" class="block w-full text-left">
                                <img src="<?php echo e($event['file_url']); ?>" alt="" class="max-h-56 w-full rounded-md object-cover" loading="lazy">
                            </button>
                        <?php else: ?>
                            <a href="<?php echo e($event['download_url'] ?? '#'); ?>" class="flex items-center gap-2 px-2 py-2 text-sm text-[#027eb5] hover:underline">
                                <span class="text-lg" aria-hidden="true">📄</span>
                                <span class="truncate"><?php echo e($event['body']); ?></span>
                            </a>
                        <?php endif; ?>
                        <?php if($caption !== ''): ?>
                            <?php $parsedCaption = \App\Support\Client\ClientChatMessagePresenter::splitQuote($caption); ?>
                            <div class="border-t border-black/5 px-2 py-1.5 text-[14px] leading-snug text-slate-900">
                                <?php if($parsedCaption['quoted']): ?>
                                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'mb-1.5 rounded-md border-l-2 px-2 py-1 text-[12px] leading-snug',
                                        'border-emerald-700/30 bg-emerald-900/5 text-emerald-900/60' => $isOutgoing,
                                        'border-slate-300 bg-slate-100 text-slate-500' => ! $isOutgoing,
                                    ]); ?>">
                                        <p class="line-clamp-4 whitespace-pre-wrap"><?php echo e($parsedCaption['quoted']); ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if($parsedCaption['body'] !== ''): ?>
                                    <p class="whitespace-pre-wrap"><?php echo e($parsedCaption['body']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php $parsed = \App\Support\Client\ClientChatMessagePresenter::splitQuote((string) ($event['body'] ?? '')); ?>
                        <?php if($parsed['quoted']): ?>
                            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'mb-1.5 rounded-md border-l-2 px-2 py-1 text-[12px] leading-snug',
                                'border-emerald-700/30 bg-emerald-900/5 text-emerald-900/60' => $isOutgoing,
                                'border-slate-300 bg-slate-100 text-slate-500' => ! $isOutgoing,
                            ]); ?>">
                                <p class="line-clamp-4 whitespace-pre-wrap"><?php echo e($parsed['quoted']); ?></p>
                            </div>
                        <?php endif; ?>
                        <p class="whitespace-pre-wrap break-words"><?php echo e($parsed['body'] !== '' ? $parsed['body'] : ($parsed['quoted'] ? '' : ($event['body'] ?? ''))); ?></p>
                    <?php endif; ?>
                    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'flex items-center justify-end gap-1 px-2 pb-0.5 text-[11px]',
                        'text-emerald-800/70' => $isOutgoing,
                        'text-slate-500' => ! $isOutgoing,
                    ]); ?>">
                        <span title="<?php echo e($event['at']->format('d M Y H:i')); ?>"><?php echo e($event['at']->format('H:i')); ?></span>
                        <?php if($isOutgoing): ?><span aria-hidden="true">✓✓</span><?php endif; ?>
                    </p>
                </div>

                <?php if($canDelete || $isAttachment || ! $isAttachment): ?>
                    <div class="absolute <?php echo e($isOutgoing ? 'right-0 -left-8' : 'left-0 -right-8'); ?> top-1/2 hidden -translate-y-1/2 group-hover:flex">
                        <details class="relative text-xs">
                            <summary class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-full bg-white/90 text-slate-600 shadow ring-1 ring-slate-200 [&::-webkit-details-marker]:hidden">▾</summary>
                            <div class="absolute <?php echo e($isOutgoing ? 'right-8' : 'left-8'); ?> top-0 z-20 min-w-[9rem] rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                <?php if(! $isAttachment): ?>
                                    <button type="button" class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" @click="copyText(<?php echo \Illuminate\Support\Js::from($event['body'])->toHtml() ?>)"><?php echo e(__('Copy')); ?></button>
                                <?php endif; ?>
                                <?php if($isImage): ?>
                                    <button type="button" class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" @click="openLightbox(<?php echo \Illuminate\Support\Js::from($event['file_url'])->toHtml() ?>)"><?php echo e(__('View')); ?></button>
                                    <a href="<?php echo e($event['download_url']); ?>" class="block px-3 py-1.5 hover:bg-slate-50" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"><?php echo e(__('Download')); ?></a>
                                <?php endif; ?>
                                <?php if($canDelete && $isAttachment): ?>
                                    <form method="POST" action="<?php echo e(route('admin.communications.inbox.attachments.destroy', [$active, $event['attachment_id']])); ?>" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Remove this file from the chat?'))->toHtml() ?>)">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="block w-full px-3 py-1.5 text-left text-red-600 hover:bg-red-50"><?php echo e(__('Delete')); ?></button>
                                    </form>
                                <?php endif; ?>
                                <?php if($canDelete && ! $isAttachment && ! empty($event['message_id'])): ?>
                                    <form method="POST" action="<?php echo e(route('admin.communications.inbox.messages.destroy', [$active, $event['message_id']])); ?>" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this message?'))->toHtml() ?>)">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="block w-full px-3 py-1.5 text-left text-red-600 hover:bg-red-50"><?php echo e(__('Delete')); ?></button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="block w-full px-3 py-1.5 text-left text-slate-500 hover:bg-slate-50" @click="$dispatch('open-attachments-tab')"><?php echo e(__('All media')); ?></button>
                            </div>
                        </details>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="py-12 text-center text-sm text-slate-500/90"><?php echo e(__('No messages yet. Say hello or send a photo below.')); ?></p>
    <?php endif; ?>

    <div x-show="lightboxUrl" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click="closeLightbox()">
        <img :src="lightboxUrl" alt="" class="max-h-[90vh] max-w-full rounded-lg object-contain" @click.stop>
        <button type="button" class="absolute right-4 top-4 rounded-full bg-white/20 px-3 py-1 text-white" @click="closeLightbox()">✕</button>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\chat-messages.blade.php ENDPATH**/ ?>