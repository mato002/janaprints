<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold">{{ __('AR aging as of :date', ['date' => $profile['aging']['as_of_date'] ?? now()->toDateString()]) }}</h3>
    <dl class="space-y-2 text-sm">
        @foreach ([
            'current' => __('Current'),
            '1_30' => __('1–30 days overdue'),
            '31_60' => __('31–60 days overdue'),
            '61_90' => __('61–90 days overdue'),
            '90_plus' => __('90+ days overdue'),
        ] as $key => $label)
            <div class="flex justify-between">
                <dt class="text-slate-500">{{ $label }}</dt>
                <dd class="font-mono">{{ number_format($aging[$key] ?? 0, 2) }}</dd>
            </div>
        @endforeach
        <div class="flex justify-between border-t border-erp-border pt-2 font-semibold">
            <dt>{{ __('Total open AR') }}</dt>
            <dd class="font-mono">{{ number_format($profile['aging']['total'] ?? 0, 2) }}</dd>
        </div>
        <div class="flex justify-between pt-2 text-slate-600">
            <dt>{{ __('Ledger outstanding') }}</dt>
            <dd class="font-mono">{{ number_format($profile['outstanding'] ?? 0, 2) }}</dd>
        </div>
    </dl>
</x-admin.card>
