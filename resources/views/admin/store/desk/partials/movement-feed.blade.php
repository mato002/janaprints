<x-admin.card :padding="false" class="mb-4">
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Recent stock movements') }}</h2>
        <p class="mt-0.5 text-xs text-slate-500">{{ __('Live feed of posted movements today and earlier.') }}</p>
    </div>
    @if (count($movementFeed ?? []) === 0)
        <div class="px-4 py-6 text-center text-sm text-slate-500">{{ __('No stock movements recorded yet.') }}</div>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($movementFeed as $movement)
                <li class="flex items-center gap-4 px-4 py-3 text-sm">
                    <span class="w-12 shrink-0 font-mono text-xs tabular-nums text-slate-500">{{ $movement['time'] }}</span>
                    <span @class([
                        'inline-flex shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                        'border-emerald-200 bg-emerald-50 text-emerald-700' => $movement['inbound'],
                        'border-rose-200 bg-rose-50 text-rose-700' => ! $movement['inbound'],
                    ])>{{ $movement['type'] }}</span>
                    <span class="min-w-0 flex-1 truncate font-medium text-slate-900">{{ $movement['item'] }}</span>
                    <span @class([
                        'shrink-0 font-mono text-xs font-semibold tabular-nums',
                        'text-emerald-700' => $movement['inbound'],
                        'text-rose-700' => ! $movement['inbound'],
                    ])>{{ $movement['quantity'] }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</x-admin.card>
