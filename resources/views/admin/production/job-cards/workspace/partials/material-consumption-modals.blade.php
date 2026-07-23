@php
    use App\Support\Production\MaterialRequirementsService;

    $requirements = collect($tabData['material_requirements'] ?? []);
    $qtyHints = [];
    $warehouseHints = [];

    foreach ($requirements as $row) {
        $requirement = $row['requirement'] ?? null;
        $itemId = $requirement?->inventory_item_id;

        if (! $itemId) {
            continue;
        }

        $remaining = (float) ($row['remaining'] ?? 0);

        if ($remaining > 0) {
            $qtyHints[(int) $itemId] = $remaining;
            $warehouseHints[(int) $itemId] = $requirement->warehouse_id;
        }
    }

    $usingBomSuggestions = false;

    if ($qtyHints === []) {
        $suggestions = app(MaterialRequirementsService::class)->suggestQuantities($jobCard);
        $usingBomSuggestions = $suggestions !== [];

        foreach ($suggestions as $itemId => $hint) {
            $qtyHints[(int) $itemId] = (float) ($hint['quantity'] ?? 0);
            if (! empty($hint['warehouse_id'])) {
                $warehouseHints[(int) $itemId] = (int) $hint['warehouse_id'];
            }
        }
    }

    $defaultRequirement = $requirements->first(fn ($row) => ($row['remaining'] ?? 0) > 0) ?? $requirements->first();
    $defaultItemId = old('inventory_item_id', $defaultRequirement['requirement']->inventory_item_id ?? (array_key_first($qtyHints) ?: null));
    $defaultWarehouseId = old(
        'warehouse_id',
        $warehouseHints[(int) $defaultItemId] ?? $defaultRequirement['requirement']->warehouse_id ?? ($tabData['warehouses'][0]->id ?? null),
    );
    $defaultQty = old('quantity', $qtyHints[(int) $defaultItemId] ?? ($defaultRequirement['remaining'] ?? null));
@endphp

@if ($tabData['can_consume'] ?? false)
    <dialog id="record-consumption-modal" class="erp-modal w-full max-w-lg rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
        <form
            method="POST"
            action="{{ route('admin.inventory.production.consume', $jobCard) }}"
            class="p-5"
            data-consumption-form
            data-qty-hints='@json($qtyHints)'
            data-warehouse-hints='@json($warehouseHints)'
        >
            @csrf
            <h3 class="text-base font-semibold text-slate-900">{{ __('Record consumption') }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ __('Deduct raw materials (paper, ink, etc.) from a physical warehouse — not the finished product.') }}</p>

            @if ($requirements->isEmpty() && ! $usingBomSuggestions)
                <p class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">{{ __('No material requirements on this job yet, and no BOM quantity could be suggested. Generate requirements from the Materials tab, or enter quantity manually. Stock must exist in the warehouse first (Supply Chain → Direct Stock Receipts).') }}</p>
            @elseif ($requirements->isEmpty() && $usingBomSuggestions)
                <p class="mt-3 rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-900">{{ __('Quantity is prefilled from the product BOM × order qty. Generate formal requirements on the Materials tab when you are ready to track remaining balances.') }}</p>
            @elseif ($requirements->contains(fn ($row) => ($row['remaining'] ?? 0) > 0))
                <p class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">{{ __('Quantity prefills with remaining requirement for the selected material. Prefer Consume on the Materials tab for each line when possible.') }}</p>
            @endif

            <div class="mt-4 grid grid-cols-1 gap-3">
                <div>
                    <label class="erp-label">{{ __('Raw material') }}</label>
                    <select name="inventory_item_id" class="erp-select w-full" required data-consumption-item>
                        @forelse ($tabData['inventory_items'] ?? [] as $inv)
                            <option
                                value="{{ $inv->id }}"
                                @selected((string) $defaultItemId === (string) $inv->id)
                                @if (isset($qtyHints[(int) $inv->id])) data-suggested-qty="{{ $qtyHints[(int) $inv->id] }}" @endif
                                @if (isset($warehouseHints[(int) $inv->id])) data-suggested-warehouse="{{ $warehouseHints[(int) $inv->id] }}" @endif
                            >{{ $inv->sku }} — {{ $inv->item_name }}</option>
                        @empty
                            <option value="">{{ __('No raw materials found') }}</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Physical warehouse') }}</label>
                    <select name="warehouse_id" class="erp-select w-full" required data-consumption-warehouse>
                        @foreach ($tabData['warehouses'] ?? [] as $wh)
                            <option value="{{ $wh->id }}" @selected((string) $defaultWarehouseId === (string) $wh->id)>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Quantity') }}</label>
                    <input type="number" step="0.001" name="quantity" class="erp-input w-full" value="{{ $defaultQty }}" placeholder="{{ __('Qty') }}" required data-consumption-qty>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="erp-btn-secondary" data-close-dialog="record-consumption-modal">{{ __('Cancel') }}</button>
                <button type="submit" class="erp-btn-primary">{{ __('Record consumption') }}</button>
            </div>
        </form>
    </dialog>
@endif

@if ($tabData['can_record_waste'] ?? false)
    <dialog id="record-waste-modal" class="erp-modal w-full max-w-lg rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
        <form method="POST" action="{{ route('admin.production.job-cards.wastage.store', $jobCard) }}" class="p-5">
            @csrf
            <input type="hidden" name="flow_type" value="waste">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Record waste') }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ __('Capture production waste against this job for costing and yield tracking.') }}</p>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="erp-label">{{ __('Material') }}</label>
                    <select name="inventory_item_id" class="erp-select w-full" required>
                        @foreach ($tabData['inventory_items'] ?? [] as $inv)
                            <option value="{{ $inv->id }}" @selected((string) $defaultItemId === (string) $inv->id)>{{ $inv->sku }} — {{ $inv->item_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Warehouse') }}</label>
                    <select name="warehouse_id" class="erp-select w-full" required>
                        @foreach ($tabData['warehouses'] ?? [] as $wh)
                            <option value="{{ $wh->id }}" @selected((string) $defaultWarehouseId === (string) $wh->id)>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Waste type') }}</label>
                    <select name="waste_type" class="erp-select w-full" required>
                        @foreach (\App\Enums\ProductionWasteType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label">{{ __('Quantity') }}</label>
                    <input type="number" step="0.001" name="quantity" class="erp-input w-full" value="{{ old('quantity', $defaultQty) }}" required>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="erp-btn-secondary" data-close-dialog="record-waste-modal">{{ __('Cancel') }}</button>
                <button type="submit" class="erp-btn-primary">{{ __('Record waste') }}</button>
            </div>
        </form>
    </dialog>

    <dialog id="record-return-modal" class="erp-modal w-full max-w-lg rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
        <form method="POST" action="{{ route('admin.production.job-cards.wastage.store', $jobCard) }}" class="p-5">
            @csrf
            <input type="hidden" name="flow_type" value="return">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Record material return') }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ __('Return unused material from the shop floor back to stock.') }}</p>

            <div class="mt-4 grid grid-cols-1 gap-3">
                <div>
                    <label class="erp-label">{{ __('Material') }}</label>
                    <select name="inventory_item_id" class="erp-select w-full" required>
                        @foreach ($tabData['inventory_items'] ?? [] as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->sku }} — {{ $inv->item_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Warehouse') }}</label>
                    <select name="warehouse_id" class="erp-select w-full" required>
                        @foreach ($tabData['warehouses'] ?? [] as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Quantity') }}</label>
                    <input type="number" step="0.001" name="quantity" class="erp-input w-full" required>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="erp-btn-secondary" data-close-dialog="record-return-modal">{{ __('Cancel') }}</button>
                <button type="submit" class="erp-btn-secondary">{{ __('Record return') }}</button>
            </div>
        </form>
    </dialog>
@endif
