<div
    x-show="!selectedKey"
    x-cloak
    class="designer-desk-idle"
>
    <div class="rounded-xl border border-erp-border bg-white px-6 py-10 text-center shadow-sm">
        @if (! $has_assignments)
            <p class="text-base font-semibold text-slate-900">{{ __('You\'re all caught up') }}</p>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">{{ __('No jobs assigned. The next artwork request will appear automatically.') }}</p>
        @else
            <p class="text-base font-semibold text-slate-900">{{ __('Select a job') }}</p>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">{{ __('Pick a card from Today\'s queue to preview artwork, specs, and actions here.') }}</p>
        @endif
    </div>

    @if (count($today_activity) > 0)
        <section class="mt-3 rounded-xl border border-erp-border bg-white shadow-sm" aria-label="{{ __('Recent activity') }}">
            <div class="border-b border-erp-border px-3 py-2">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('Recent activity') }}</h3>
            </div>
            <ul class="divide-y divide-slate-100 px-3">
                @foreach ($today_activity as $event)
                    @php
                        $dot = match ($event['tone'] ?? 'neutral') {
                            'success' => 'bg-emerald-500',
                            'warning' => 'bg-amber-400',
                            'info' => 'bg-blue-500',
                            default => 'bg-slate-300',
                        };
                    @endphp
                    <li class="flex items-start gap-3 py-2.5 text-sm">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
                        <span class="w-16 shrink-0 font-mono text-[11px] tabular-nums text-slate-400">{{ $event['time'] }}</span>
                        <span class="text-slate-700">{{ $event['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
