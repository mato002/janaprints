@if (count($lowStockItems ?? []) > 0)
    <x-admin.card :padding="false" class="mb-4">
        <div class="flex items-center justify-between gap-2 border-b border-erp-border px-4 py-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('Low stock') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Most urgent items right now.') }}</p>
            </div>
            <a href="{{ $reorderAlertsUrl ?? route('admin.store.desk.reorder-alerts') }}" class="text-xs font-medium text-erp-accent hover:underline" data-erp-modal-open>{{ __('View all') }}</a>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach ($lowStockItems as $item)
                <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                    <span class="min-w-0">
                        <span class="block font-medium text-slate-900">{{ $item['name'] }}</span>
                        @if (! empty($item['warehouse']))
                            <span class="block text-xs text-slate-500">{{ $item['warehouse'] }}</span>
                        @endif
                    </span>
                    <span class="inline-flex shrink-0 items-center gap-1.5">
                        <span @class([
                            'font-semibold tabular-nums',
                            'text-rose-700' => $item['urgent'],
                            'text-amber-700' => ! $item['urgent'],
                        ])>{{ $item['remaining_label'] }}</span>
                        @if ($item['urgent'])
                            <span aria-hidden="true" class="text-amber-500">⚠</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </x-admin.card>
@endif
