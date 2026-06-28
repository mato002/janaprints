@php
    $consumptions = $tabData['consumptions'] ?? null;
    $wastage = $tabData['wastage'] ?? [];
    $sessionWaste = $tabData['session_waste'] ?? [];
    $serialSpoilage = $tabData['serial_spoilage'] ?? [];
@endphp

<x-admin.card class="mb-4">
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Waste consolidation') }}</h3>
    <dl class="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
        <div><dt class="text-slate-500">{{ __('Material waste') }}</dt><dd class="tabular-nums">{{ $wastage['metrics']['material_wasted'] ?? 0 }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Session waste') }}</dt><dd class="tabular-nums">{{ $sessionWaste['total_waste'] ?? 0 }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Serial spoilage') }}</dt><dd class="tabular-nums">{{ $serialSpoilage['spoiled_quantity'] ?? 0 }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Returned') }}</dt><dd class="tabular-nums">{{ $wastage['metrics']['material_returned'] ?? 0 }}</dd></div>
    </dl>
</x-admin.card>

@if ($tabData['can_consume'] ?? false)
    <x-admin.card class="mb-4" id="consume-material">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Record consumption') }}</h3>
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
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Record') }}</button>
        </form>
    </x-admin.card>
@endif

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Consumption history') }}</h3>
    @if ($consumptions && $consumptions->count() > 0)
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Unit') }}</th>
                        <th>{{ __('Cost') }}</th>
                        <th>{{ __('Warehouse') }}</th>
                        <th>{{ __('By') }}</th>
                        <th>{{ __('At') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consumptions as $row)
                        <tr>
                            <td>{{ $row->inventoryItem?->item_name }} <span class="text-slate-500">({{ $row->inventoryItem?->sku }})</span></td>
                            <td class="tabular-nums">{{ $row->quantity }}</td>
                            <td>{{ $row->inventoryItem?->unitOfMeasure?->code ?? '—' }}</td>
                            <td class="tabular-nums">{{ $row->unit_cost !== null ? number_format((float) $row->quantity * (float) $row->unit_cost, 2) : '—' }}</td>
                            <td>{{ $row->warehouse?->name ?? '—' }}</td>
                            <td>{{ $row->consumer?->name ?? '—' }}</td>
                            <td>{{ $row->consumed_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($consumptions->hasPages())
            <div class="mt-4">{{ $consumptions->links() }}</div>
        @endif
    @else
        <x-admin.empty-state :title="__('No consumption recorded')" :description="__('Consumption is captured during production sessions or manually from requirements.')" />
    @endif
</x-admin.card>
