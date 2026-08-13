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
    $hasRequirements = (bool) ($tabData['has_requirements'] ?? false);
    $showWorkflow = ! empty($steps) && ! $hasRequirements;
    $showShortages = ($tabData['has_shortages'] ?? false) || ($hasRequirements && ($tabData['reservable_count'] ?? 0) > 0);
    $showConsume = $hasRequirements && (($tabData['pending_consume_count'] ?? 0) > 0);
    $emptyDescription = match ($currentKey) {
        'link_product' => __('Link the finished product first, then add a BOM and generate requirements.'),
        'bom' => __('Create a BOM for the finished product, then generate requirements.'),
        'generate' => __('Choose a warehouse and generate requirements from the BOM.'),
        default => __('Requirements are snapshotted from the product catalog BOM when the job card is created, or can be generated manually.'),
    };
@endphp

<div class="job-360-materials">
    @if ($showWorkflow)
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-erp-primary">{{ __('Materials') }}</h3>
                <p class="mt-1 text-xs text-slate-600">
                    {{ __('Use the highlighted step to add a BOM or generate requirements on this job.') }}
                </p>
            </div>
            @if (! empty($workflow['blocker']))
                <p class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-950">
                    {{ $workflow['blocker'] }}
                </p>
            @endif
        </div>

        <ol class="job-360-materials__steps">
            @foreach ($steps as $index => $step)
                @php
                    $status = $step['status'] ?? 'blocked';
                @endphp
                <li @class([
                    'job-360-materials__step',
                    'job-360-materials__step--done' => $status === 'done',
                    'job-360-materials__step--current' => $status === 'current',
                    'job-360-materials__step--blocked' => $status === 'blocked',
                ])>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('Step :n', ['n' => $index + 1]) }}
                        @if ($status === 'done')
                            <span class="ml-1 text-emerald-700">✓ {{ __('Done') }}</span>
                        @elseif ($status === 'current')
                            <span class="ml-1 text-erp-accent">{{ __('Next') }}</span>
                        @endif
                    </p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $step['title'] }}</p>
                    <p class="mt-1 text-xs leading-snug text-slate-600">{{ $step['detail'] }}</p>
                </li>
            @endforeach
        </ol>

        @if ($currentKey === 'link_product' && $canLinkProduct)
            <div class="job-360-materials__action">
                <form
                    method="POST"
                    action="{{ route('admin.production.job-cards.finished-product', $jobCard) }}"
                    class="flex flex-wrap items-end gap-2"
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
            </div>
        @endif

        @if ($currentKey === 'bom' && $canCreateBom)
            <form
                method="POST"
                action="{{ route('admin.production.job-cards.materials.bom', $jobCard) }}"
                class="space-y-3"
                data-turbo-frame="erp-main"
            >
                @csrf
                @if ($errors->any())
                    <div class="job-360-materials__action rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-900" data-erp-validation-errors>
                        <p class="font-medium">{{ __('Could not create the bill of materials.') }}</p>
                        <ul class="mt-1 list-disc space-y-0.5 pl-4">
                            @foreach ($errors->all() as $error)
                                <li data-erp-validation-message>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @include('admin.production.boms._form', [
                    'bom' => null,
                    'finishedItems' => $tabData['bom_finished_items'] ?? collect(),
                    'rawMaterials' => $tabData['bom_raw_materials'] ?? collect(),
                    'preselectedFinishedItemId' => $tabData['bom_preselected_finished_item_id'] ?? null,
                    'prefilledName' => $tabData['bom_prefilled_name'] ?? null,
                    'suggestedLines' => $tabData['bom_suggested_lines'] ?? null,
                    'splitLayout' => true,
                ])
                <div class="job-360-materials__action flex justify-end">
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Create BOM') }}</button>
                </div>
            </form>
        @elseif ($currentKey === 'bom')
            <p class="job-360-materials__action text-xs text-amber-800">
                {{ __('You need permission to create a bill of materials on this job.') }}
            </p>
        @endif

        @if ($currentKey === 'generate' && $canShowGenerateForm)
            <div class="job-360-materials__action">
                <form
                    method="POST"
                    action="{{ route('admin.production.job-cards.materials.generate', $jobCard) }}"
                    class="flex flex-wrap items-end gap-2"
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
            </div>
        @endif
    @endif

    <div class="job-360-materials__meta">
        <x-admin.card class="job-360-materials__costs">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Material cost summary') }}</h3>
            <dl class="grid grid-cols-2 gap-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Estimated') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['estimated_material_cost'] ?? 0), 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Issued') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['issued_material_cost'] ?? 0), 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Consumed') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['consumed_material_cost'] ?? 0), 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Waste') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) ($costs['waste_cost'] ?? 0), 2) }}</dd></div>
            </dl>
            @if ($hasRequirements && $canShowGenerateForm)
                <form method="POST" action="{{ route('admin.production.job-cards.materials.generate', $jobCard) }}" class="mt-3 flex flex-wrap items-end gap-2 border-t border-erp-border pt-3">
                    @csrf
                    <div class="min-w-[10rem] flex-1">
                        <label class="erp-label text-xs">{{ __('Warehouse') }}</label>
                        <select name="warehouse_id" class="erp-input w-full text-sm" required>
                            @foreach ($tabData['warehouses'] ?? [] as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Regenerate requirements') }}</button>
                </form>
            @endif
        </x-admin.card>

        @if ($showShortages || $showConsume || ! $hasRequirements)
        <div @class([
            'job-360-materials__alert grid grid-cols-1 gap-3',
            'lg:grid-cols-2' => $showShortages && $showConsume,
        ])>
            @if ($showShortages)
                <x-admin.card id="materials-shortages" class="border-amber-200 bg-amber-50/70">
                    <div class="flex flex-col gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-amber-950">
                                @if ($tabData['has_shortages'] ?? false)
                                    {{ __('Material shortages (:count)', ['count' => $tabData['short_count'] ?? 0]) }}
                                @else
                                    {{ __('Reserve materials for this job') }}
                                @endif
                            </h3>
                            <p class="mt-1 text-xs text-amber-900/80">
                                @if ($tabData['has_shortages'] ?? false)
                                    {{ __('Stock on hand is below what this job needs. Reserve whatever is available now, then receive the shortfall into warehouse stock.') }}
                                @else
                                    {{ __('Stock is available. Reserve it against this job so other jobs cannot take it.') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if (($tabData['can_reserve'] ?? false) && ($tabData['reservable_count'] ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.production.job-cards.materials.reserve-all', $jobCard) }}">
                                    @csrf
                                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Reserve all available') }}</button>
                                </form>
                            @endif
                            @if ($tabData['can_receive_stock'] ?? false)
                                <a href="{{ $tabData['receipts_url'] }}" class="erp-btn-secondary text-sm" data-erp-modal-open>
                                    {{ __('Receive stock') }}
                                </a>
                            @elseif ($tabData['has_shortages'] ?? false)
                                <p class="self-center text-xs text-amber-900">{{ __('Ask store / inventory to receive the short materials.') }}</p>
                            @endif
                        </div>
                    </div>

                    @if (! empty($tabData['shortages']))
                        <ul class="mt-3 space-y-2 border-t border-amber-200/80 pt-3 text-sm">
                            @foreach ($tabData['shortages'] as $line)
                                @php
                                    $qty = rtrim(rtrim(number_format((float) $line['shortfall'], 3, '.', ''), '0'), '.');
                                    $unit = $line['unit'] ? ' '.$line['unit'] : '';
                                    $available = rtrim(rtrim(number_format((float) ($line['available'] ?? 0), 3, '.', ''), '0'), '.');
                                    $required = rtrim(rtrim(number_format((float) ($line['required'] ?? 0), 3, '.', ''), '0'), '.');
                                @endphp
                                <li class="rounded-md border border-amber-200/70 bg-white/80 px-3 py-2">
                                    <span class="font-medium text-slate-900">
                                        {{ $line['item'] }}
                                        @if (! empty($line['sku']))
                                            <span class="text-xs font-normal text-slate-500">({{ $line['sku'] }})</span>
                                        @endif
                                    </span>
                                    <p class="mt-0.5 text-xs text-amber-950">
                                        {{ __('Need :qty:unit more', ['qty' => $qty, 'unit' => $unit]) }}
                                        <span class="text-slate-500">· {{ __('Have :available / need :required', ['available' => $available, 'required' => $required]) }}</span>
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin.card>
            @endif

            @if ($showConsume)
                <x-admin.card id="materials-consume" class="border-sky-200 bg-sky-50/70">
                    <div class="flex flex-col gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-sky-950">
                                {{ __('Record material consumption (:count)', ['count' => $tabData['pending_consume_count'] ?? 0]) }}
                            </h3>
                            <p class="mt-1 text-xs text-sky-900/80">
                                @if (($tabData['consumable_count'] ?? 0) > 0)
                                    {{ __('Finished goods posting needs consumption recorded. Consume all remaining lines in one step (qty is capped by available stock).') }}
                                @else
                                    {{ __('Consumption is still required, but warehouse stock is zero for these lines. Receive stock first, then consume.') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if (($tabData['can_consume'] ?? false) && ($tabData['consumable_count'] ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.production.job-cards.materials.consume-all', $jobCard) }}">
                                    @csrf
                                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Consume all remaining') }}</button>
                                </form>
                            @elseif ($tabData['can_receive_stock'] ?? false)
                                <a href="{{ $tabData['receipts_url'] }}" class="erp-btn-secondary text-sm" data-erp-modal-open>
                                    {{ __('Receive stock') }}
                                </a>
                            @elseif (! ($tabData['can_consume'] ?? false))
                                <p class="self-center text-xs text-sky-900">{{ __('You need production consume and inventory issue permissions to record consumption.') }}</p>
                            @endif
                        </div>
                    </div>
                </x-admin.card>
            @endif

            @if (! $hasRequirements && ! $showShortages && ! $showConsume)
                <x-admin.card>
                    <x-admin.empty-state
                        :title="__('No material requirements')"
                        :description="$emptyDescription"
                    />
                </x-admin.card>
            @endif
        </div>
        @endif
    </div>

    @if ($hasRequirements)
        <x-admin.card class="job-360-materials__table">
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
                                    <td>
                                        {{ $row['item_name'] }} <span class="text-slate-500">({{ $row['sku'] }})</span>
                                        @if (! empty($row['stock_warehouse_name']))
                                            <div class="text-xs text-slate-500">{{ $row['stock_warehouse_name'] }}</div>
                                        @elseif (! empty($row['warehouse_name']))
                                            <div class="text-xs text-slate-500">{{ $row['warehouse_name'] }}</div>
                                        @endif
                                    </td>
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
                                            @if (($row['remaining'] ?? 0) > 0 && ($row['available'] ?? 0) > 0)
                                                @php
                                                    $consumeQty = min((float) $row['remaining'], (float) $row['available']);
                                                @endphp
                                                <form method="POST" action="{{ route('admin.production.job-cards.materials.consume', [$jobCard, $row['requirement']]) }}" class="inline-flex items-center gap-1">
                                                    @csrf
                                                    <input type="number" step="0.001" min="0.001" name="quantity" class="erp-input w-20 text-xs" value="{{ $consumeQty }}">
                                                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Consume') }}</button>
                                                </form>
                                            @elseif (($row['remaining'] ?? 0) > 0)
                                                <span class="text-xs text-amber-700">{{ __('No stock') }}</span>
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
    @endif
</div>
