@props(['logs', 'compact' => false])

@php
    $items = $logs instanceof \Illuminate\Support\Collection
        ? app(\App\Support\Communications\CommunicationLogService::class)->timelinePayload($logs)
        : $logs;
@endphp

<ul class="communication-timeline space-y-0" role="list">
    @forelse ($items as $item)
        <li class="relative flex gap-4 {{ $compact ? 'py-3' : 'py-4' }} pl-6">
            <span class="absolute left-0 top-4 flex h-3 w-3 items-center justify-center">
                <span class="h-2 w-2 rounded-full bg-erp-accent ring-4 ring-erp-accent/10"></span>
            </span>
            @if (! $loop->last)
                <span class="absolute left-[5px] top-5 h-full w-px bg-erp-border" aria-hidden="true"></span>
            @endif
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <a href="{{ $item['url'] }}" data-turbo-frame="erp-main" class="text-sm font-semibold text-erp-primary hover:text-erp-accent">
                            {{ $item['subject'] ?: Str::limit($item['message'], 48) }}
                        </a>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $item['channel_label'] }} · {{ $item['type_label'] }}
                            @if ($item['recipient'])
                                · {{ $item['recipient'] }}
                            @endif
                        </p>
                    </div>
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $item['status_badge'] }}">
                        {{ $item['status_label'] }}
                    </span>
                </div>
                @unless ($compact)
                    <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $item['message'] }}</p>
                @endunless
                <p class="mt-1 text-[10px] text-slate-400">
                    {{ $item['reference_number'] }}
                    · {{ \Illuminate\Support\Carbon::parse($item['created_at'])->diffForHumans() }}
                </p>
            </div>
        </li>
    @empty
        <li class="py-6 text-center text-sm text-slate-500">{{ __('No communications recorded yet.') }}</li>
    @endforelse
</ul>
