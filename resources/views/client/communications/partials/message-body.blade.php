@php
    $parsed = \App\Support\Client\ClientChatMessagePresenter::splitQuote((string) ($body ?? ''));
    $isOutgoing = (bool) ($outgoing ?? false);
@endphp

@if ($parsed['quoted'] || $parsed['quoted_author'])
    <div @class([
        'client-chat__quote',
        'client-chat__quote--out' => $isOutgoing,
        'client-chat__quote--in' => ! $isOutgoing,
    ]) role="note" aria-label="{{ __('Quoted message') }}">
        @if ($parsed['quoted_author'])
            <p class="client-chat__quote-author">{{ $parsed['quoted_author'] }}</p>
        @endif
        @if ($parsed['quoted'])
            <p class="client-chat__quote-text">{{ $parsed['quoted'] }}</p>
        @endif
    </div>
@endif

@if ($parsed['body'] !== '')
    <p class="client-chat__text">{{ $parsed['body'] }}</p>
@elseif (! $parsed['quoted'] && ! $parsed['quoted_author'])
    <p class="client-chat__text">{{ $body }}</p>
@endif
