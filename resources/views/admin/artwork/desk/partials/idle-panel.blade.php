<div
    x-show="!selectedKey"
    x-cloak
    class="designer-desk-idle mt-6"
>
    @if (! $has_assignments)
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 px-6 py-8 text-center">
            <p class="text-sm font-medium text-slate-600">{{ __('No artwork assigned.') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('New jobs will appear in your queue when assigned.') }}</p>
        </div>
    @endif

    @if (count($today_activity) > 0)
        <div class="mt-4 rounded-xl border border-erp-border bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __("Today's Activity") }}</h3>
            <div class="divide-y divide-slate-100">
                @foreach ($today_activity as $event)
                    @php
                        $icon = match ($event['tone'] ?? 'neutral') {
                            'success' => '✓',
                            'warning' => '!',
                            'info' => '↑',
                            default => '•',
                        };
                        $iconClass = match ($event['tone'] ?? 'neutral') {
                            'success' => 'text-emerald-600',
                            'warning' => 'text-amber-600',
                            'info' => 'text-blue-600',
                            default => 'text-slate-400',
                        };
                    @endphp
                    <div class="flex items-start gap-3 py-2.5 text-sm">
                        <span class="mt-0.5 w-4 shrink-0 text-center font-bold {{ $iconClass }}">{{ $icon }}</span>
                        <span class="w-12 shrink-0 font-mono text-xs text-slate-400">{{ $event['time'] }}</span>
                        <span class="text-slate-700">{{ $event['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif (! $has_assignments)
        <div class="mt-4 rounded-xl border border-erp-border bg-white p-4 text-sm text-slate-500">
            {{ __('No recent activity yet.') }}
        </div>
    @endif
</div>
