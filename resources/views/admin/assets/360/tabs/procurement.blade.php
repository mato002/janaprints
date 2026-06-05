<x-admin.card>
    <dl class="mb-4 grid grid-cols-2 gap-3 text-sm">
        <div><dt class="text-slate-500">{{ __('Vendor') }}</dt><dd>{{ $tabData['vendor']?->vendor_name ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Purchase Order') }}</dt><dd>{{ $tabData['purchase_order']?->po_number ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Goods Receipt') }}</dt><dd>{{ $tabData['goods_receipt']?->receipt_number ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Capitalization') }}</dt><dd>{{ $tabData['capitalization']?->candidate_number ?? '—' }}</dd></div>
    </dl>
    <h3 class="mb-3 text-sm font-semibold">{{ __('Acquisition Timeline') }}</h3>
    <ul class="space-y-2 text-sm">
        @forelse ($tabData['timeline'] as $event)
            <li class="flex justify-between border-b border-erp-border pb-2">
                <span>{{ $event['event'] }} — {{ $event['ref'] }}</span>
                <span class="text-slate-500">{{ optional($event['date'])->format('Y-m-d') }}</span>
            </li>
        @empty
            <li class="text-slate-500">{{ __('No procurement history.') }}</li>
        @endforelse
    </ul>
</x-admin.card>
