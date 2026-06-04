@php($consumptions = $tabData['consumptions'] ?? null)

<x-admin.card class="mb-4 border-amber-200 bg-amber-50">
    <p class="text-sm text-amber-900">{{ $tabData['bom_warning'] ?? '' }}</p>
    <p class="mt-1 text-sm text-amber-800">{{ $tabData['material_requirements_placeholder'] ?? '' }}</p>
</x-admin.card>

@php($wastage = $tabData['wastage'] ?? [])
@if (! ($wastage['activated'] ?? false))
    <x-admin.card class="mb-4 border-dashed">
        <h3 class="mb-1 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Wastage') }}</h3>
        <p class="text-sm text-slate-600">{{ $wastage['placeholder'] ?? __('Wastage Tracking Not Activated') }}</p>
    </x-admin.card>
@endif

@if ($tabData['can_consume'] ?? false)
    <x-admin.card class="mb-6" id="consume-material">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Consume material') }}</h3>
        <form method="POST" action="{{ route('admin.inventory.production.consume', $jobCard) }}" class="grid grid-cols-1 gap-2 md:grid-cols-4">
            @csrf
            <select name="inventory_item_id" class="erp-input text-sm" required>
                @foreach ($tabData['inventory_items'] ?? [] as $inv)
                    <option value="{{ $inv->id }}">{{ $inv->sku }} — {{ $inv->item_name }}</option>
                @endforeach
            </select>
            <select name="warehouse_id" class="erp-input text-sm" required>
                @foreach ($tabData['warehouses'] ?? [] as $wh)
                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>
            <input type="number" step="0.001" name="quantity" class="erp-input text-sm" placeholder="{{ __('Qty') }}" required>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Record consumption') }}</button>
        </form>
    </x-admin.card>
@endif

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Materials consumed') }}</h3>
    @if ($consumptions && $consumptions->count() > 0)
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Unit') }}</th>
                        <th>{{ __('Unit cost') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Warehouse') }}</th>
                        <th>{{ __('Movement') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consumptions as $row)
                        @php($lineTotal = $row->unit_cost ? (float) $row->quantity * (float) $row->unit_cost : null)
                        <tr>
                            <td>{{ $row->inventoryItem?->item_name ?? '—' }} <span class="text-slate-500">({{ $row->inventoryItem?->sku }})</span></td>
                            <td class="tabular-nums">{{ $row->quantity }}</td>
                            <td>{{ $row->inventoryItem?->unitOfMeasure?->code ?? '—' }}</td>
                            <td class="tabular-nums">{{ $row->unit_cost !== null ? number_format((float) $row->unit_cost, 2) : '—' }}</td>
                            <td class="tabular-nums">{{ $lineTotal !== null ? number_format($lineTotal, 2) : '—' }}</td>
                            <td>{{ $row->warehouse?->name ?? '—' }}</td>
                            <td>{{ $row->movement?->reference ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($consumptions->hasPages())
            <div class="mt-4">{{ $consumptions->links() }}</div>
        @endif
    @else
        <x-admin.empty-state :title="__('No material consumption')" :description="__('Record material issues against this job when inventory is issued.')" />
    @endif
</x-admin.card>
