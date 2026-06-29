@php $events = $tabData['events'] ?? []; @endphp

<div class="space-y-3">
    @forelse ($events as $event)
        <article class="flex flex-col gap-1 border-l-2 border-erp-border pl-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-900">
                    {{ $event['label'] }}
                    @if (! empty($event['is_current']))
                        <span class="erp-badge ml-1">{{ __('Current') }}</span>
                    @endif
                </p>
                @if (! empty($event['detail']))
                    <p class="text-sm text-slate-600">{{ $event['detail'] }}</p>
                @endif
            </div>
            <div class="text-xs text-slate-500 sm:text-right">
                <p>{{ $event['at'] ? \Illuminate\Support\Carbon::parse($event['at'])->format('Y-m-d H:i') : '—' }}</p>
                @if (! empty($event['user']))
                    <p>{{ $event['user'] }}</p>
                @endif
            </div>
        </article>
    @empty
        <p class="text-sm text-slate-500">{{ __('No timeline events yet.') }}</p>
    @endforelse
</div>
