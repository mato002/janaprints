@if (count($reorderRecommendations ?? []) > 0)
    <x-admin.card :padding="false" class="mb-4">
        <div class="flex items-center justify-between gap-2 border-b border-erp-border px-4 py-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('Recommended purchases') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Replenishment suggestions from reorder intelligence.') }}</p>
            </div>
            <a href="{{ $reorderAlertsUrl ?? route('admin.store.desk.reorder-alerts') }}" class="text-xs font-medium text-erp-accent hover:underline" data-erp-modal-open>{{ __('Alerts') }}</a>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach ($reorderRecommendations as $rec)
                <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                    <span class="font-medium text-slate-900">{{ $rec['name'] }}</span>
                    <span class="inline-flex shrink-0 items-center gap-2">
                        <span @class([
                            'rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                            'border-rose-200 bg-rose-50 text-rose-800' => $rec['urgency'] === __('Order today'),
                            'border-amber-200 bg-amber-50 text-amber-800' => $rec['urgency'] === __('Order tomorrow'),
                            'border-slate-200 bg-slate-50 text-slate-700' => $rec['urgency'] === __('Monitor'),
                        ])>{{ $rec['urgency'] }}</span>
                        <span class="text-xs text-slate-500">{{ $rec['action'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </x-admin.card>
@endif
