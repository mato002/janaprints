@php $inv = $dashboard['inventory']; @endphp
<section class="exec-panel">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Inventory Health') }}</h2>
        <span class="text-[11px] font-medium text-erp-primary">{{ $inv['inventory_value'] }}</span>
    </div>
    <div class="grid grid-cols-2 gap-2 text-[11px]">
        <div>
            <p class="mb-1 font-semibold text-slate-600">{{ __('Low stock') }} ({{ count($inv['low_stock']) }})</p>
            <ul class="space-y-0.5 text-slate-600">
                @forelse ($inv['low_stock'] as $item)
                    <li>{{ $item['name'] }} <span class="text-slate-400">({{ $item['sku'] }})</span></li>
                @empty
                    <li class="text-slate-400">—</li>
                @endforelse
            </ul>
        </div>
        <div>
            <p class="mb-1 font-semibold text-slate-600">{{ __('Out of stock') }} ({{ count($inv['out_of_stock']) }})</p>
            <ul class="space-y-0.5 text-slate-600">
                @forelse ($inv['out_of_stock'] as $item)
                    <li>{{ $item['name'] }}</li>
                @empty
                    <li class="text-slate-400">—</li>
                @endforelse
            </ul>
        </div>
        <div class="col-span-2">
            <p class="mb-1 font-semibold text-slate-600">{{ __('Fast moving') }}</p>
            <ul class="flex flex-wrap gap-x-3 gap-y-0.5 text-slate-600">
                @forelse ($inv['fast_moving'] as $item)
                    <li>{{ $item['name'] }} <span class="text-slate-400">{{ number_format($item['issued'], 0) }}</span></li>
                @empty
                    <li class="text-slate-400">—</li>
                @endforelse
            </ul>
        </div>
    </div>
    <p class="mt-2 text-[10px] text-slate-500">{{ __('Reorder alerts') }}: <strong>{{ $inv['reorder_alerts'] }}</strong></p>
</section>
