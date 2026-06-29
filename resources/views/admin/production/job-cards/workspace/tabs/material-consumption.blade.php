@php
    $consumptions = $tabData['consumptions'] ?? null;
    $wastage = $tabData['wastage'] ?? [];
    $sessionWaste = $tabData['session_waste'] ?? [];
    $serialSpoilage = $tabData['serial_spoilage'] ?? [];
    $requirements = collect($tabData['material_requirements'] ?? []);
    $defaultRequirement = $requirements->first(fn ($row) => ($row['remaining'] ?? 0) > 0) ?? $requirements->first();
    $defaultItemId = old('inventory_item_id', $defaultRequirement['requirement']->inventory_item_id ?? null);
    $defaultWarehouseId = old('warehouse_id', $defaultRequirement['requirement']->warehouse_id ?? ($tabData['warehouses'][0]->id ?? null));
    $defaultQty = old('quantity', $defaultRequirement['remaining'] ?? null);
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
        <h3 class="mb-1 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Record consumption') }}</h3>
        <p class="mb-3 text-xs text-slate-500">{{ __('Deduct raw materials from a physical warehouse. When this job has material requirements, consumption counts toward that requirement and cannot exceed the remaining quantity.') }}</p>
        @if ($requirements->contains(fn ($row) => ($row['remaining'] ?? 0) > 0))
            <p class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">{{ __('Prefer the Consume button on the Materials tab for each requirement line. Manual entry here is capped to the same remaining quantity.') }}</p>
        @endif
        <form method="POST" action="{{ route('admin.inventory.production.consume', $jobCard) }}" class="grid grid-cols-1 gap-2 md:grid-cols-4">
            @csrf
            <div>
                <label class="erp-label text-xs">{{ __('Material') }}</label>
                <select name="inventory_item_id" class="erp-input w-full text-sm" required>
                    @foreach ($tabData['inventory_items'] ?? [] as $inv)
                        <option value="{{ $inv->id }}" @selected((string) $defaultItemId === (string) $inv->id)>{{ $inv->sku }} — {{ $inv->item_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label text-xs">{{ __('Physical warehouse') }}</label>
                <select name="warehouse_id" class="erp-input w-full text-sm" required>
                    @foreach ($tabData['warehouses'] ?? [] as $wh)
                        <option value="{{ $wh->id }}" @selected((string) $defaultWarehouseId === (string) $wh->id)>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label text-xs">{{ __('Quantity') }}</label>
                <input type="number" step="0.001" name="quantity" class="erp-input w-full text-sm" value="{{ $defaultQty }}" placeholder="{{ __('Qty') }}" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="erp-btn-primary w-full text-sm">{{ __('Record') }}</button>
            </div>
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
