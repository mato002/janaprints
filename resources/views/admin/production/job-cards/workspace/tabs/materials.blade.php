@php
    $requirements = $tabData['requirements'] ?? collect();
    $costs = $tabData['costs'] ?? [];
@endphp

<x-admin.card class="mb-4">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Material cost summary') }}</h3>
    <dl class="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
        <div><dt class="text-slate-500">{{ __('Estimated') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['estimated_material_cost'] ?? 0), 2) }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Issued') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['issued_material_cost'] ?? 0), 2) }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Consumed') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['consumed_material_cost'] ?? 0), 2) }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Waste') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['waste_cost'] ?? 0), 2) }}</dd></div>
    </dl>
</x-admin.card>

@if (($tabData['can_generate'] ?? false) && ! ($tabData['has_requirements'] ?? false))
    <x-admin.card class="mb-4">
        <form method="POST" action="{{ route('admin.production.job-cards.materials.generate', $jobCard) }}" class="flex flex-wrap items-end gap-2">
            @csrf
            <div class="min-w-[12rem]">
                <label class="erp-label text-xs">{{ __('Warehouse') }}</label>
                <select name="warehouse_id" class="erp-input w-full text-sm" required>
                    @foreach ($tabData['warehouses'] ?? [] as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Generate requirements') }}</button>
        </form>
    </x-admin.card>
@endif

@if ($tabData['can_reserve'] ?? false)
    <form method="POST" action="{{ route('admin.production.job-cards.materials.reserve-all', $jobCard) }}" class="mb-4">
        @csrf
        <button type="submit" class="erp-btn-secondary text-sm">{{ __('Reserve all available') }}</button>
    </form>
@endif

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Material requirements') }}</h3>
    @if ($requirements->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Material') }}</th>
                        <th>{{ __('Required') }}</th>
                        <th>{{ __('Available') }}</th>
                        <th>{{ __('Shortfall') }}</th>
                        <th>{{ __('Issued') }}</th>
                        <th>{{ __('Consumed') }}</th>
                        <th>{{ __('Waste') }}</th>
                        <th>{{ __('Returned') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requirements as $row)
                        <tr>
                            <td>{{ $row['item_name'] }} <span class="text-slate-500">({{ $row['sku'] }})</span></td>
                            <td class="tabular-nums">{{ $row['required'] }} {{ $row['unit'] }}</td>
                            <td class="tabular-nums">{{ $row['available'] }}</td>
                            <td class="tabular-nums {{ $row['shortfall'] > 0 ? 'text-red-600 font-medium' : '' }}">{{ $row['shortfall'] }}</td>
                            <td class="tabular-nums">{{ $row['issued'] }}</td>
                            <td class="tabular-nums">{{ $row['consumed'] }}</td>
                            <td class="tabular-nums">{{ $row['waste'] }}</td>
                            <td class="tabular-nums">{{ $row['returned'] }}</td>
                            <td><span class="erp-badge text-xs">{{ $row['status']->label() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-admin.empty-state :title="__('No material requirements')" :description="__('Requirements are snapshotted from the product catalog BOM when the job card is created, or can be generated manually.')" />
    @endif
</x-admin.card>
