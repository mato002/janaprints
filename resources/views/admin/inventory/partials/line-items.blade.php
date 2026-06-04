@php($fields = $formFields ?? [])
<h3 class="font-medium mt-4">{{ __('Lines') }}</h3>
<div class="space-y-2">
    <div class="grid grid-cols-4 gap-2 text-sm font-medium text-slate-500">
        @if (($fields['inventory_item_id']['visible'] ?? true))<span>{{ $fields['inventory_item_id']['label'] ?? __('Item') }}</span>@endif
        @if (($fields['quantity']['visible'] ?? true))<span>{{ $fields['quantity']['label'] ?? __('Qty') }}</span>@endif
        @if (($fields['unit_cost']['visible'] ?? true))<span>{{ $fields['unit_cost']['label'] ?? __('Unit cost') }}</span>@endif
        @if (!empty($directions))<span>{{ __('Direction') }}</span>@endif
    </div>
    @for ($i = 0; $i < ($lineCount ?? 3); $i++)
        <div class="grid grid-cols-4 gap-2">
            @if (($fields['inventory_item_id']['visible'] ?? true))
                <select name="items[{{ $i }}][inventory_item_id]" class="erp-input">
                    <option value="">{{ __('—') }}</option>
                    @foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->sku }} — {{ $item->item_name }}</option>@endforeach
                </select>
            @endif
            @if (($fields['quantity']['visible'] ?? true))
                <input type="number" step="0.001" min="0.001" name="items[{{ $i }}][quantity]" class="erp-input" placeholder="0">
            @endif
            @if (($fields['unit_cost']['visible'] ?? true))
                <input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_cost]" class="erp-input" placeholder="0">
            @endif
            @if (!empty($directions))
                <select name="items[{{ $i }}][direction]" class="erp-input">
                    @foreach ($directions as $d)<option value="{{ $d->value }}">{{ $d->value }}</option>@endforeach
                </select>
            @endif
        </div>
    @endfor
</div>
