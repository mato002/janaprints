@forelse ($events as $event)
    @php
        $type = $event['type'] ?? 'message';
        $isOutgoing = $type === 'message' && ($event['direction'] ?? '') === 'outgoing';
        $isIncoming = $type === 'message' && ($event['direction'] ?? '') === 'incoming';
        $isNote = $type === 'internal_note';
        $isErp = $type === 'erp';
        $isSystem = in_array($type, ['system', 'audit'], true);
        $channel = $event['channel'] ?? null;
        $channelEnum = $channel ? \App\Enums\InboxMessageChannel::tryFrom($channel) : null;
    @endphp
    <div class="mb-3 flex {{ $isOutgoing ? 'justify-end' : ($isSystem || $isErp ? 'justify-center' : 'justify-start') }}">
        @if ($isSystem || $isErp)
            <div class="max-w-[92%] rounded-lg border border-slate-200 bg-white/90 px-3 py-2 text-center text-xs text-slate-600 shadow-sm">
                <p class="font-semibold text-slate-700">{{ $event['at']->format('d M H:i') }} · {{ $event['title'] }}</p>
                <p class="mt-0.5">
                    @if (! empty($event['url']))
                        <a href="{{ $event['url'] }}" class="text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $event['body'] }}</a>
                    @else
                        {{ $event['body'] }}
                    @endif
                </p>
                @if (! empty($event['meta']))<p class="text-[10px] text-slate-400">{{ $event['meta'] }}</p>@endif
            </div>
        @elseif ($isNote)
            <div class="max-w-[88%] rounded-lg border-2 border-dashed border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-950 shadow-sm">
                <p class="flex items-center gap-1 text-[10px] font-bold uppercase text-amber-800">
                    <span class="rounded bg-amber-200 px-1">{{ __('Internal') }}</span>
                    {{ $event['at']->format('H:i') }}
                </p>
                <p class="mt-1 whitespace-pre-wrap">{{ $event['body'] }}</p>
                @if (! empty($event['tags']))
                    <p class="mt-1 flex flex-wrap gap-1">
                        @foreach ($event['tags'] as $tag)<span class="rounded bg-amber-200/80 px-1 text-[10px]">#{{ $tag }}</span>@endforeach
                    </p>
                @endif
                @if (! empty($event['meta']))<p class="mt-1 text-[10px] text-amber-700">{{ $event['meta'] }}</p>@endif
            </div>
        @else
            <div @class([
                'max-w-[75%] px-3 py-1.5 text-sm shadow-sm',
                'rounded-lg rounded-br-none bg-[#d9fdd3] text-slate-900' => $isOutgoing,
                'rounded-lg rounded-bl-none bg-white text-slate-900' => $isIncoming,
            ])>
                <p class="whitespace-pre-wrap">{{ $event['body'] }}</p>
                <p class="mt-0.5 text-right text-[11px] text-slate-500">{{ $event['at']->format('H:i') }}</p>
            </div>
        @endif
    </div>
@empty
    <p class="py-12 text-center text-sm text-slate-500">{{ __('No activity yet. Send a message or add an internal note.') }}</p>
@endforelse
