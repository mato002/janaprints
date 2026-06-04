<x-admin.card>
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Profitability Alerts') }}</h2>
    <div class="divide-y divide-erp-border">
        @foreach ($dashboard['alerts'] as $alert)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-erp-primary">{{ $alert['title'] }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ __(':count items in current scope', ['count' => $alert['count']]) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-admin.status-badge :variant="$alert['severity'] === 'danger' ? 'danger' : ($alert['severity'] === 'warning' ? 'warning' : 'neutral')">
                        {{ $alert['severity_label'] }}
                    </x-admin.status-badge>
                    <span class="text-lg font-bold tabular-nums text-erp-primary">{{ $alert['count'] }}</span>
                    @if ($alert['url'] && $alert['count'] > 0)
                        <a href="{{ $alert['url'] }}" class="text-xs font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('Filter') }}</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-admin.card>
