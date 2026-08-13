@php
    $requirements = $tabData['requirements'] ?? collect();
    $costs = $tabData['costs'] ?? [];
    $missingBoms = $tabData['missing_boms'] ?? [];
    $canCreateBom = (bool) ($tabData['can_create_bom'] ?? false);
    $emptyDescription = $canCreateBom
        ? __('Create a BOM for the finished product, then generate requirements.')
        : __('Requirements are snapshotted from the product catalog BOM when the job card is created, or can be generated manually.');
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

@if ($canCreateBom && count($missingBoms) > 0)
    <x-admin.card class="mb-4 border-amber-200 bg-amber-50/60">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-amber-950">{{ __('No active BOM for this job’s finished product') }}</h3>
                <p class="mt-1 text-xs text-amber-900/80">
                    {{ __('Add the bill of materials here, then generate requirements — no need to leave Job 360.') }}
                </p>
                <ul class="mt-2 space-y-1 text-xs text-amber-950">
                    @foreach ($missingBoms as $missing)
                        <li class="truncate">{{ $missing['label'] }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                @foreach ($missingBoms as $missing)
                    @if (! empty($missing['create_url']))
                        <a href="{{ $missing['create_url'] }}" class="erp-btn-primary text-sm" data-erp-modal-open>
                            {{ count($missingBoms) === 1 ? __('Add BOM') : __('Add BOM: :item', ['item' => $missing['label']]) }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </x-admin.card>
@endif

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
            <button type="submit" class="erp-btn-primary text-sm" @disabled($canCreateBom && count($missingBoms) > 0)>
                {{ __('Generate requirements') }}
            </button>
        </form>
        @if ($canCreateBom && count($missingBoms) > 0)
            <p class="mt-2 text-xs text-slate-500">{{ __('Generate requirements after the BOM is created.') }}</p>
        @endif
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
                        @if ($tabData['can_consume'] ?? false)
                            <th></th>
                        @endif
                        @if ($tabData['can_reserve'] ?? false)
                            <th></th>
                        @endif
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
                            @if ($tabData['can_consume'] ?? false)
                                <td class="whitespace-nowrap">
                                    @if (($row['remaining'] ?? 0) > 0)
                                        <form method="POST" action="{{ route('admin.production.job-cards.materials.consume', [$jobCard, $row['requirement']]) }}" class="inline-flex items-center gap-1">
                                            @csrf
                                            <input type="number" step="0.001" min="0.001" name="quantity" class="erp-input w-20 text-xs" value="{{ $row['remaining'] }}">
                                            <button type="submit" class="erp-btn-primary text-xs">{{ __('Consume') }}</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            @endif
                            @if ($tabData['can_reserve'] ?? false)
                                <td class="whitespace-nowrap">
                                    @if ($row['can_reserve'] ?? false)
                                        <form method="POST" action="{{ route('admin.production.job-cards.materials.reserve', [$jobCard, $row['requirement']]) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="erp-btn-secondary text-xs">{{ __('Reserve') }}</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-admin.empty-state
            :title="__('No material requirements')"
            :description="$emptyDescription"
        />
    @endif
</x-admin.card>
