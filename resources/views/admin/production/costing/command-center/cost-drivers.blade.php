<x-admin.card>
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Cost Driver Breakdown') }}</h2>

    @if ($dashboard['cost_drivers']['has_data'])
        @php
            $total = max(1, (float) $dashboard['cost_drivers']['total']);
        @endphp
        <div class="space-y-3">
            @foreach ($dashboard['cost_drivers']['available'] as $driver)
                @php $share = round(((float) $driver['amount'] / $total) * 100, 1); @endphp
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-erp-primary">{{ $driver['label'] }}</span>
                        <span class="tabular-nums text-slate-600">KES {{ number_format($driver['amount'], 0) }} <span class="text-xs text-slate-400">({{ $share }}%)</span></span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-erp-accent" style="width: {{ min(100, $share) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-xs text-slate-500">{{ __('Total production cost in scope: KES :amount', ['amount' => number_format($dashboard['cost_drivers']['total'], 0)]) }}</p>
    @else
        <p class="text-sm text-slate-500">{{ __('Cost driver breakdown will appear once detailed costing inputs are available.') }}</p>
    @endif
</x-admin.card>
