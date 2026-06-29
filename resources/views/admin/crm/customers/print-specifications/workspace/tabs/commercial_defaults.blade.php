<div class="grid gap-4 sm:grid-cols-2 text-sm">
    <div><p class="text-slate-500">{{ __('Commercial notes') }}</p><p class="whitespace-pre-wrap">{{ $tabData['commercial_notes'] ?: '—' }}</p></div>
    <div><p class="text-slate-500">{{ __('Default quantity') }}</p><p class="tabular-nums">{{ $tabData['default_quantity'] ?? '—' }}</p></div>
    <div><p class="text-slate-500">{{ __('Default unit price') }}</p><p class="tabular-nums">{{ $tabData['default_unit_price'] !== null ? number_format((float) $tabData['default_unit_price'], 2) : '—' }}</p></div>
    <div><p class="text-slate-500">{{ __('Default billing type') }}</p><p>{{ $tabData['default_billing_type'] ?? '—' }}</p></div>
    <div><p class="text-slate-500">{{ __('Default fulfilment') }}</p><p>{{ $tabData['default_fulfilment_method'] ?? '—' }}</p></div>
</div>
