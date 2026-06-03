<h3 class="font-medium mt-4">{{ __('Lines') }}</h3>
<div class="space-y-2">
    <div class="grid grid-cols-4 gap-2 text-sm font-medium text-slate-500"><span>{{ __('Item') }}</span><span>{{ __('Qty') }}</span><span>{{ __('Unit cost') }}</span>@if (!empty($directions))<span>{{ __('Direction') }}</span>@endif</div>
    @for ($i = 0; $i < 3; $i++)
        <div class="grid grid-cols-4 gap-2">
            <select name="items[{{ $i }}][inventory_item_id]" class="erp-input">
                <option value="">{{ __('—') }}</option>
                @foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->sku }} — {{ $item->item_name }}</option>@endforeach
            </select>
            <input type="number" step="0.001" name="items[{{ $i }}][quantity]" class="erp-input" placeholder="0">
            <input type="number" step="0.01" name="items[{{ $i }}][unit_cost]" class="erp-input" placeholder="0">
            @if (!empty($directions))
                <select name="items[{{ $i }}][direction]" class="erp-input">
                    @foreach ($directions as $d)<option value="{{ $d->value }}">{{ $d->value }}</option>@endforeach
                </select>
            @endif
        </div>
    @endfor
</div>
