@php
    $canManage = auth()->user()->can('reply', App\Models\Communications\Inbox\CommunicationConversation::class);
    $canManageFiles = auth()->user()->can('attachments', App\Models\Communications\Inbox\CommunicationConversation::class);
@endphp

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
    @forelse ($events as $event)
        @php
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
        @endphp
        <div id="{{ $domId }}" class="group mb-3 flex {{ $isOutgoing ? 'justify-end' : 'justify-start' }}">
            <div class="relative max-w-[min(70%,420px)]">
                <div @class([
                    'overflow-hidden',
                    'shared-inbox__msg-out' => $isOutgoing && ! $isImage,
                    'shared-inbox__msg-in' => ! $isOutgoing && ! $isImage,
                    'rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-200/60' => $isImage,
                ])>
                    @if ($isAttachment)
                        @if ($isImage)
                            <button type="button" @click="openLightbox(@js($event['file_url']))" class="block w-full text-left">
                                <img src="{{ $event['file_url'] }}" alt="" class="max-h-56 w-full rounded-md object-cover" loading="lazy">
                            </button>
                        @else
                            <a href="{{ $event['download_url'] ?? '#' }}" class="flex items-center gap-2 px-2 py-2 text-sm text-[#027eb5] hover:underline">
                                <span class="text-lg" aria-hidden="true">📄</span>
                                <span class="truncate">{{ $event['body'] }}</span>
                            </a>
                        @endif
                        @if ($caption !== '')
                            @php $parsedCaption = \App\Support\Client\ClientChatMessagePresenter::splitQuote($caption); @endphp
                            <div class="border-t border-black/5 px-2 py-1.5 text-[14px] leading-snug text-slate-900">
                                @if ($parsedCaption['quoted'])
                                    <div @class([
                                        'mb-1.5 rounded-md border-l-2 px-2 py-1 text-[12px] leading-snug',
                                        'border-emerald-700/30 bg-emerald-900/5 text-emerald-900/60' => $isOutgoing,
                                        'border-slate-300 bg-slate-100 text-slate-500' => ! $isOutgoing,
                                    ])>
                                        <p class="line-clamp-4 whitespace-pre-wrap">{{ $parsedCaption['quoted'] }}</p>
                                    </div>
                                @endif
                                @if ($parsedCaption['body'] !== '')
                                    <p class="whitespace-pre-wrap">{{ $parsedCaption['body'] }}</p>
                                @endif
                            </div>
                        @endif
                    @else
                        @php $parsed = \App\Support\Client\ClientChatMessagePresenter::splitQuote((string) ($event['body'] ?? '')); @endphp
                        @if ($parsed['quoted'])
                            <div @class([
                                'mb-1.5 rounded-md border-l-2 px-2 py-1 text-[12px] leading-snug',
                                'border-emerald-700/30 bg-emerald-900/5 text-emerald-900/60' => $isOutgoing,
                                'border-slate-300 bg-slate-100 text-slate-500' => ! $isOutgoing,
                            ])>
                                <p class="line-clamp-4 whitespace-pre-wrap">{{ $parsed['quoted'] }}</p>
                            </div>
                        @endif
                        <p class="whitespace-pre-wrap break-words">{{ $parsed['body'] !== '' ? $parsed['body'] : ($parsed['quoted'] ? '' : ($event['body'] ?? '')) }}</p>
                    @endif
                    <p @class([
                        'flex items-center justify-end gap-1 px-2 pb-0.5 text-[11px]',
                        'text-emerald-800/70' => $isOutgoing,
                        'text-slate-500' => ! $isOutgoing,
                    ])>
                        <span title="{{ $event['at']->format('d M Y H:i') }}">{{ $event['at']->format('H:i') }}</span>
                        @if ($isOutgoing)<span aria-hidden="true">✓✓</span>@endif
                    </p>
                </div>

                @if ($canDelete || $isAttachment || ! $isAttachment)
                    <div class="absolute {{ $isOutgoing ? 'right-0 -left-8' : 'left-0 -right-8' }} top-1/2 hidden -translate-y-1/2 group-hover:flex">
                        <details class="relative text-xs">
                            <summary class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-full bg-white/90 text-slate-600 shadow ring-1 ring-slate-200 [&::-webkit-details-marker]:hidden">▾</summary>
                            <div class="absolute {{ $isOutgoing ? 'right-8' : 'left-8' }} top-0 z-20 min-w-[9rem] rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                @if (! $isAttachment)
                                    <button type="button" class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" @click="copyText(@js($event['body']))">{{ __('Copy') }}</button>
                                @endif
                                @if ($isImage)
                                    <button type="button" class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" @click="openLightbox(@js($event['file_url']))">{{ __('View') }}</button>
                                    <a href="{{ $event['download_url'] }}" class="block px-3 py-1.5 hover:bg-slate-50" data-turbo-frame="{{ $inboxTurboFrame }}">{{ __('Download') }}</a>
                                @endif
                                @if ($canDelete && $isAttachment)
                                    <form method="POST" action="{{ route('admin.communications.inbox.attachments.destroy', [$active, $event['attachment_id']]) }}" data-turbo-frame="{{ $inboxTurboFrame }}" onsubmit="return confirm(@js(__('Remove this file from the chat?')))">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="block w-full px-3 py-1.5 text-left text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                    </form>
                                @endif
                                @if ($canDelete && ! $isAttachment && ! empty($event['message_id']))
                                    <form method="POST" action="{{ route('admin.communications.inbox.messages.destroy', [$active, $event['message_id']]) }}" data-turbo-frame="{{ $inboxTurboFrame }}" onsubmit="return confirm(@js(__('Delete this message?')))">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="block w-full px-3 py-1.5 text-left text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                    </form>
                                @endif
                                <button type="button" class="block w-full px-3 py-1.5 text-left text-slate-500 hover:bg-slate-50" @click="$dispatch('open-attachments-tab')">{{ __('All media') }}</button>
                            </div>
                        </details>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <p class="py-12 text-center text-sm text-slate-500/90">{{ __('No messages yet. Say hello or send a photo below.') }}</p>
    @endforelse

    <div x-show="lightboxUrl" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click="closeLightbox()">
        <img :src="lightboxUrl" alt="" class="max-h-[90vh] max-w-full rounded-lg object-contain" @click.stop>
        <button type="button" class="absolute right-4 top-4 rounded-full bg-white/20 px-3 py-1 text-white" @click="closeLightbox()">✕</button>
    </div>
</div>
