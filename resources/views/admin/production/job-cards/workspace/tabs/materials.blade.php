@php
    $requirements = $tabData['requirements'] ?? collect();
    $costs = $tabData['costs'] ?? [];
    $workflow = $tabData['workflow'] ?? [];
    $missingBoms = $tabData['missing_boms'] ?? [];
    $canCreateBom = (bool) ($tabData['can_create_bom'] ?? false);
    $canLinkProduct = (bool) ($tabData['can_link_product'] ?? false);
    $canShowGenerateForm = (bool) ($tabData['can_show_generate_form'] ?? false);
    $canGenerate = (bool) ($tabData['can_generate'] ?? false);
    $steps = $workflow['steps'] ?? [];
    $currentKey = $workflow['current_key'] ?? null;
    $emptyDescription = match ($currentKey) {
        'link_product' => __('Link the finished product first, then add a BOM and generate requirements.'),
        'bom' => __('Create a BOM for the finished product, then generate requirements.'),
        'generate' => __('Choose a warehouse and generate requirements from the BOM.'),
        default => __('Requirements are snapshotted from the product catalog BOM when the job card is created, or can be generated manually.'),
    };
@endphp

@if (! empty($steps) && ! ($tabData['has_requirements'] ?? false))
    <x-admin.card class="mb-4 border-slate-200">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-erp-primary">{{ __('Materials workflow') }}</h3>
                <p class="mt-1 text-xs text-slate-600">
                    {{ __('Complete these steps in order. The next action stays on this page — you should not discover blockers only after clicking Generate.') }}
                </p>
            </div>
            @if (! empty($workflow['blocker']))
                <p class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-950">
                    {{ $workflow['blocker'] }}
                </p>
            @endif
        </div>

        <ol class="space-y-3">
            @foreach ($steps as $index => $step)
                @php
                    $status = $step['status'] ?? 'blocked';
                @endphp
                <li @class([
                    'rounded-lg border px-3 py-3',
                    'border-emerald-200 bg-emerald-50/50' => $status === 'done',
                    'border-erp-accent/40 bg-erp-accent/5 ring-1 ring-erp-accent/20' => $status === 'current',
                    'border-slate-200 bg-slate-50/60 opacity-70' => $status === 'blocked',
                ])>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {{ __('Step :n', ['n' => $index + 1]) }}
                                @if ($status === 'done')
                                    <span class="ml-2 text-emerald-700">✓ {{ __('Done') }}</span>
                                @elseif ($status === 'current')
                                    <span class="ml-2 text-erp-accent">{{ __('Next') }}</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $step['title'] }}</p>
                            <p class="mt-1 text-xs text-slate-600">{{ $step['detail'] }}</p>
                        </div>
                    </div>

                    @if ($status === 'current' && ($step['key'] ?? '') === 'link_product' && $canLinkProduct)
                        <form
                            method="POST"
                            action="{{ route('admin.production.job-cards.finished-product', $jobCard) }}"
                            class="mt-3 flex flex-wrap items-end gap-2 border-t border-slate-200/80 pt-3"
                        >
                            @csrf
                            <div class="min-w-[16rem] flex-1">
                                <label class="erp-label required text-xs" for="finished_inventory_item_id">{{ __('Finished product') }}</label>
                                <select id="finished_inventory_item_id" name="inventory_item_id" class="erp-input w-full text-sm" required>
                                    <option value="">{{ __('Select catalogue finished good') }}</option>
                                    @foreach ($tabData['finished_items'] ?? [] as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="erp-btn-primary text-sm">{{ __('Link product') }}</button>
                        </form>
                        @if (($tabData['finished_items'] ?? collect())->isEmpty())
                            <p class="mt-2 text-xs text-amber-800">
                                {{ __('No finished-good catalogue items found for this branch. Create one under Inventory first.') }}
                            </p>
                        @endif
                    @endif

                    @if ($status === 'current' && ($step['key'] ?? '') === 'bom' && $canCreateBom)
                        <div class="mt-3 flex flex-wrap gap-2 border-t border-slate-200/80 pt-3">
                            @foreach ($missingBoms as $missing)
                                @if (! empty($missing['create_url']))
                                    <a href="{{ $missing['create_url'] }}" class="erp-btn-primary text-sm" data-erp-modal-open>
                                        {{ count($missingBoms) === 1 ? __('Add BOM') : __('Add BOM: :item', ['item' => $missing['label']]) }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if ($status === 'current' && ($step['key'] ?? '') === 'generate' && $canShowGenerateForm)
                        <form
                            method="POST"
                            action="{{ route('admin.production.job-cards.materials.generate', $jobCard) }}"
                            class="mt-3 flex flex-wrap items-end gap-2 border-t border-slate-200/80 pt-3"
                        >
                            @csrf
                            <div class="min-w-[12rem]">
                                <label class="erp-label required text-xs" for="materials_warehouse_id">{{ __('Warehouse') }}</label>
                                <select id="materials_warehouse_id" name="warehouse_id" class="erp-input w-full text-sm" required>
                                    @foreach ($tabData['warehouses'] ?? [] as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="erp-btn-primary text-sm" @disabled(! $canGenerate)>
                                {{ __('Generate requirements') }}
                            </button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ol>
    </x-admin.card>
@endif

<x-admin.card class="mb-4">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Material cost summary') }}</h3>
    <dl class="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
        <div><dt class="text-slate-500">{{ __('Estimated') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['estimated_material_cost'] ?? 0), 2) }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Issued') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['issued_material_cost'] ?? 0), 2) }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Consumed') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['consumed_material_cost'] ?? 0), 2) }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Waste') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['waste_cost'] ?? 0), 2) }}</dd></div>
    </dl>
</x-admin.card>

@if (($tabData['has_requirements'] ?? false) && $canShowGenerateForm)
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
            <button type="submit" class="erp-btn-secondary text-sm">{{ __('Regenerate requirements') }}</button>
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
