@props(['stages', 'focus_label'])

<x-admin.card class="mb-6">
    <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ $focus_label }}</h2>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach ($stages as $stage)
            <div @class([
                'rounded-xl border p-4 transition',
                'border-erp-accent bg-erp-accent/5 shadow-sm' => $stage['highlight'] ?? false,
                'border-erp-border bg-white' => ! ($stage['highlight'] ?? false),
            ])>
                <p class="text-2xl font-bold tabular-nums text-erp-primary">{{ number_format($stage['count']) }}</p>
                <p class="mt-1 text-sm font-medium text-slate-700">{{ $stage['label'] }}</p>
                @if ($stage['conversion'])
                    <p class="mt-2 text-xs font-semibold text-emerald-700">{{ $stage['conversion'] }} {{ __('conversion') }}</p>
                    <p class="text-[11px] text-slate-500">{{ $stage['drop_off'] }} {{ __('drop-off') }}</p>
                @endif
            </div>
        @endforeach
    </div>
</x-admin.card>
