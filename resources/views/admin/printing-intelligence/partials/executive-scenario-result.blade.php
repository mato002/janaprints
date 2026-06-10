<x-admin.card>
    <h3 class="font-medium mb-3">{{ $result['scenario_label'] ?? __('Scenario') }}</h3>
    <div class="grid gap-4 md:grid-cols-2 text-sm">
        <div>
            <h4 class="text-xs uppercase text-slate-500 mb-2">{{ __('Baseline') }}</h4>
            <dl class="space-y-1">
                <div class="flex justify-between"><dt>{{ __('Revenue') }}</dt><dd>{{ number_format((float) ($result['baseline']['revenue'] ?? 0), 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('Cost') }}</dt><dd>{{ number_format((float) ($result['baseline']['cost'] ?? 0), 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('Profit') }}</dt><dd>{{ number_format((float) ($result['baseline']['profit'] ?? 0), 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('Margin') }}</dt><dd>{{ ($result['baseline']['margin_percent'] ?? null) !== null ? number_format((float) $result['baseline']['margin_percent'], 1).'%' : '—' }}</dd></div>
            </dl>
        </div>
        <div>
            <h4 class="text-xs uppercase text-slate-500 mb-2">{{ __('Simulated') }}</h4>
            <dl class="space-y-1">
                <div class="flex justify-between"><dt>{{ __('Revenue') }}</dt><dd>{{ number_format((float) ($result['simulated']['revenue'] ?? 0), 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('Cost') }}</dt><dd>{{ number_format((float) ($result['simulated']['cost'] ?? 0), 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('Profit') }}</dt><dd>{{ number_format((float) ($result['simulated']['profit'] ?? 0), 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('Margin') }}</dt><dd>{{ ($result['simulated']['margin_percent'] ?? null) !== null ? number_format((float) $result['simulated']['margin_percent'], 1).'%' : '—' }}</dd></div>
            </dl>
        </div>
    </div>
    <div class="mt-4 pt-4 border-t border-slate-100 text-sm">
        <strong>{{ __('Impact') }}:</strong>
        {{ __('Profit delta') }} {{ number_format((float) ($result['impact']['profit_delta'] ?? 0), 2) }},
        {{ __('Revenue delta') }} {{ number_format((float) ($result['impact']['revenue_delta'] ?? 0), 2) }}
    </div>
</x-admin.card>
