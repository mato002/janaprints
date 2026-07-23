@php
    $consumptions = $tabData['consumptions'] ?? null;
    $wastage = $tabData['wastage'] ?? [];
    $sessionWaste = $tabData['session_waste'] ?? [];
    $serialSpoilage = $tabData['serial_spoilage'] ?? [];
    $canConsume = (bool) ($tabData['can_consume'] ?? false);
    $canRecordWaste = (bool) ($tabData['can_record_waste'] ?? false);
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

@if ($canConsume || $canRecordWaste)
    <x-admin.card class="mb-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Material transactions') }}</h3>
                <p class="text-sm text-slate-600">{{ __('Record consumption, waste, or returns against this job.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($canConsume)
                    <button type="button" class="erp-btn-primary text-sm" data-open-dialog="record-consumption-modal">{{ __('Record consumption') }}</button>
                @endif
                @if ($canRecordWaste)
                    <button type="button" class="erp-btn-secondary text-sm" data-open-dialog="record-waste-modal">{{ __('Record waste') }}</button>
                    <button type="button" class="erp-btn-secondary text-sm" data-open-dialog="record-return-modal">{{ __('Record return') }}</button>
                @endif
            </div>
        </div>
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
        <x-admin.empty-state :title="__('No consumption recorded')" :description="__('Use Record consumption to deduct materials from stock for this job.')" />
    @endif
</x-admin.card>

@include('admin.production.job-cards.workspace.partials.material-consumption-modals', [
    'jobCard' => $jobCard,
    'tabData' => $tabData,
])
