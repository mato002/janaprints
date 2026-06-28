<div class="client-chat__messages" data-client-chat-messages>
    @php $lastDate = null; @endphp
    @forelse ($events as $event)
        @php
            $type = $event['type'] ?? 'message';
            // Inbox stores staff as outgoing and client as incoming — flip for the portal view.
            $isMine = ($event['direction'] ?? '') === 'incoming';
            $isAttachment = $type === 'attachment';
            $isImage = $isAttachment && ! empty($event['is_image']) && ! empty($event['file_url']);
            $caption = trim((string) ($event['caption'] ?? ''));
            $eventDate = $event['at']->format('Y-m-d');
            $replyText = $isAttachment
                ? ($caption !== '' ? $caption : (string) ($event['body'] ?? ''))
                : (string) ($event['body'] ?? '');
            $replyAuthor = $isMine ? __('You') : __('Jana Prints');
        @endphp

        @if ($lastDate !== $eventDate)
            @php $lastDate = $eventDate; @endphp
            <div class="client-chat__date">
                <span>{{ $event['at']->isToday() ? __('Today') : ($event['at']->isYesterday() ? __('Yesterday') : $event['at']->format('M j, Y')) }}</span>
            </div>
        @endif

        <div
            @class(['client-chat__row', 'client-chat__row--out' => $isMine, 'client-chat__row--in' => ! $isMine])
            data-chat-row
            data-message-body="{{ $replyText }}"
            data-message-author="{{ $replyAuthor }}"
            data-message-from="{{ $isMine ? 'me' : 'team' }}"
        >
            <span class="client-chat__reply-hint" data-chat-reply-hint aria-hidden="true">
                <x-client.icon name="reply" class="h-4 w-4" />
            </span>
            <div class="client-chat__bubble-wrap" data-chat-bubble-wrap>
                <div @class([
                    'client-chat__bubble',
                    'client-chat__bubble--out' => $isMine && ! $isImage,
                    'client-chat__bubble--in' => ! $isMine && ! $isImage,
                    'client-chat__bubble--media' => $isImage,
                ])>
                    @if ($isAttachment)
                        @if ($isImage)
                            <button type="button" class="client-chat__image-btn" data-client-chat-lightbox="{{ $event['file_url'] }}">
                                <img src="{{ $event['file_url'] }}" alt="" class="client-chat__image" loading="lazy">
                            </button>
                        @else
                            <a href="{{ $event['download_url'] ?? '#' }}" class="client-chat__file">
                                <x-client.icon name="document" class="h-5 w-5 shrink-0" />
                                <span class="truncate">{{ $event['body'] }}</span>
                            </a>
                        @endif
                        @if ($caption !== '')
                            <div class="client-chat__caption">
                                @include('client.communications.partials.message-body', ['body' => $caption, 'outgoing' => $isMine])
                            </div>
                        @endif
                    @else
                        @include('client.communications.partials.message-body', ['body' => $event['body'] ?? '', 'outgoing' => $isMine])
                    @endif
                    <p class="client-chat__time" title="{{ $event['at']->format('d M Y H:i') }}">
                        {{ $event['at']->format('H:i') }}
                        @if ($isMine)
                            <span class="client-chat__ticks" aria-hidden="true">
                                <x-client.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @empty
        <div class="client-chat__empty">
            <x-client.icon name="chat" class="client-chat__empty-icon" />
            <p>{{ __('No messages yet') }}</p>
            <p class="client-chat__empty-hint">{{ __('Say hello or attach artwork below to get started.') }}</p>
        </div>
    @endforelse

    <div class="client-chat__lightbox hidden" data-client-chat-lightbox-panel hidden>
        <img src="" alt="" class="client-chat__lightbox-image" data-client-chat-lightbox-image>
        <button type="button" class="client-chat__lightbox-close" data-client-chat-lightbox-close aria-label="{{ __('Close') }}">
            <x-client.icon name="x" class="h-5 w-5" />
        </button>
    </div>
</div>
