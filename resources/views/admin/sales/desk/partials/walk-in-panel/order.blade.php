@if (count($panel['warnings'] ?? []) > 0)
    <ul class="mb-3 space-y-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs">
        @foreach ($panel['warnings'] as $warning)
            <li class="flex items-start gap-1.5 text-amber-900">
                <span aria-hidden="true">⚠</span>
                <span>{{ $warning['message'] }}</span>
            </li>
        @endforeach
    </ul>
@endif

<dl class="space-y-2 text-sm">
    <div>
        <dt class="text-xs text-slate-500">{{ __('Customer') }}</dt>
        <dd class="font-medium text-slate-900">{{ $panel['customer_name'] ?? '—' }}</dd>
    </div>
    <div>
        <dt class="text-xs text-slate-500">{{ __('Specification') }}</dt>
        <dd class="font-medium text-slate-900">{{ $panel['specification_name'] ?? '—' }}</dd>
    </div>
    <div>
        <dt class="text-xs text-slate-500">{{ __('Product') }}</dt>
        <dd class="font-medium text-slate-900">{{ $panel['product'] ?? '—' }}</dd>
    </div>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <dt class="text-xs text-slate-500">{{ __('Default qty') }}</dt>
            <dd class="font-mono text-slate-900">{{ $panel['default_quantity'] ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-slate-500">{{ __('Default price') }}</dt>
            <dd class="font-mono text-slate-900">{{ $panel['default_unit_price'] ?? '—' }}</dd>
        </div>
    </div>
    <div>
        <dt class="text-xs text-slate-500">{{ __('Artwork') }}</dt>
        <dd class="font-medium">
            @if ($panel['has_artwork'] ?? false)
                <span class="text-emerald-700">✓ {{ $panel['artwork_label'] }}</span>
            @else
                <span class="text-amber-700">⚠ {{ __('Pending') }}</span>
            @endif
        </dd>
    </div>
    @if ($panel['outstanding_balance'] ?? null)
        <div>
            <dt class="text-xs text-slate-500">{{ __('Outstanding balance') }}</dt>
            <dd class="font-mono text-amber-800">{{ $panel['outstanding_balance'] }}</dd>
        </div>
    @endif
</dl>

<p class="mt-3 text-xs text-slate-500">{{ __('Quantity and price on the form are for this order. Change them only when this sale differs from the specification defaults.') }}</p>
