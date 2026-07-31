@if ($panel['customer_name'] ?? null)
    <p class="mb-3 text-xs text-slate-600">
        {{ __('Customer') }}:
        <span class="font-medium text-slate-900">{{ $panel['customer_name'] }}</span>
    </p>
@endif

@if ($panel['selected'] ?? null)
    <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50/60 px-3 py-2 text-sm">
        <p class="text-xs font-medium uppercase tracking-wide text-emerald-800">{{ __('Selected specification') }}</p>
        <p class="mt-1 font-medium text-slate-900">{{ $panel['selected']['name'] }}</p>
        <p class="text-xs text-slate-600">
            {{ $panel['selected']['code'] }}
            @if ($panel['selected']['product'] ?? null)
                · {{ $panel['selected']['product'] }}
            @endif
        </p>
        <p class="mt-1 text-xs">
            @if ($panel['selected']['has_artwork'] ?? false)
                <span class="text-emerald-700">✓ {{ __('Artwork') }}: {{ $panel['selected']['artwork_label'] }}</span>
            @else
                <span class="text-amber-700">⚠ {{ __('Artwork pending') }}</span>
            @endif
        </p>
        @if ($panel['selected']['default_unit_price'] ?? null)
            <p class="mt-1 text-xs text-slate-600">{{ __('Default price') }}: {{ $panel['selected']['default_unit_price'] }}</p>
        @endif
    </div>
@else
    <p class="mb-3 text-sm text-slate-600">
        {{ __(':count saved specification(s). Select one or create new to continue.', ['count' => $panel['saved_count'] ?? 0]) }}
    </p>
@endif

@if (count($panel['recent'] ?? []) > 0)
    <div class="border-t border-erp-border pt-3">
        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Saved specifications') }}</p>
        <ul class="space-y-2 text-xs">
            @foreach ($panel['recent'] as $spec)
                <li class="rounded border border-slate-100 bg-slate-50/80 px-2 py-1.5">
                    <p class="font-medium text-slate-900">{{ $spec['name'] }}</p>
                    <p class="text-slate-500">
                        {{ $spec['code'] }}
                        @if ($spec['product'] ?? null)
                            · {{ $spec['product'] }}
                        @endif
                    </p>
                    <p class="mt-0.5">
                        @if ($spec['has_artwork'] ?? false)
                            <span class="text-emerald-700">{{ $spec['artwork'] }}</span>
                        @else
                            <span class="text-amber-700">{{ __('No artwork') }}</span>
                        @endif
                        @if ($spec['price'] ?? null)
                            <span class="text-slate-500"> · {{ $spec['price'] }}</span>
                        @endif
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
@endif
