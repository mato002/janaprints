@php
    $rows = old('items', ($existing ?? collect())->map(fn ($line) => [
        'inventory_item_id' => $line->inventory_item_id,
        'item_classification' => $line->item_classification?->value ?? $line->item_classification ?? 'inventory_item',
        'asset_category_id' => $line->asset_category_id,
        'description' => $line->description,
        'quantity' => $line->quantity,
        'estimated_unit_cost' => $line->estimated_unit_cost ?? $line->unit_cost ?? 0,
        'unit_cost' => $line->unit_cost ?? $line->estimated_unit_cost ?? 0,
    ])->values()->all() ?: [['description' => '', 'quantity' => 1, 'estimated_unit_cost' => 0, 'unit_cost' => 0, 'item_classification' => 'inventory_item']]);
    $classifications = $classifications ?? \App\Enums\ProcurementItemClassification::cases();
    $assetCategories = $assetCategories ?? collect();
@endphp

<div>
    <h3 class="mb-2 text-sm font-semibold">{{ __('Line items') }}</h3>
    <div class="space-y-3">
        @foreach ($rows as $index => $row)
            <div class="grid gap-2 rounded border border-erp-border p-3 md:grid-cols-5">
                <select name="items[{{ $index }}][item_classification]" class="erp-select w-full">
                    @foreach ($classifications as $classification)
                        <option value="{{ $classification->value }}" @selected(($row['item_classification'] ?? 'inventory_item') === $classification->value)>{{ $classification->label() }}</option>
                    @endforeach
                </select>
                <select name="items[{{ $index }}][inventory_item_id]" class="erp-select w-full">
                    <option value="">{{ __('Manual item') }}</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" @selected(($row['inventory_item_id'] ?? '') == $item->id)>{{ $item->item_name }}</option>
                    @endforeach
                </select>
                <select name="items[{{ $index }}][asset_category_id]" class="erp-select w-full">
                    <option value="">{{ __('Asset category') }}</option>
                    @foreach ($assetCategories as $category)
                        <option value="{{ $category->id }}" @selected(($row['asset_category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <input name="items[{{ $index }}][description]" value="{{ $row['description'] ?? '' }}" class="erp-input w-full" placeholder="{{ __('Description') }}" required />
                <input name="items[{{ $index }}][quantity]" type="number" step="0.001" min="0.001" value="{{ $row['quantity'] ?? 1 }}" class="erp-input w-full" required />
                @if ($mode === 'request')
                    <input name="items[{{ $index }}][estimated_unit_cost]" type="number" step="0.01" min="0" value="{{ $row['estimated_unit_cost'] ?? 0 }}" class="erp-input w-full md:col-span-5" required />
                @else
                    <input name="items[{{ $index }}][unit_cost]" type="number" step="0.01" min="0" value="{{ $row['unit_cost'] ?? 0 }}" class="erp-input w-full md:col-span-5" required />
                @endif
            </div>
        @endforeach
    </div>
</div>
