@php
    $fields = $formFields ?? [];
    $dynamic = $dynamic ?? false;
    $directions = $directions ?? [];
    $prefilledLines = collect($prefilledLines ?? [])
        ->filter(fn ($line) => is_array($line) && filled($line['inventory_item_id'] ?? null))
        ->values()
        ->all();
    $defaultLine = ['inventory_item_id' => '', 'quantity' => '', 'unit_cost' => ''];

    if ($directions !== []) {
        $defaultLine['direction'] = $directions[0]->value ?? '';
    }

    $initialLines = collect(old('items', $prefilledLines !== [] ? $prefilledLines : [$defaultLine]))
        ->values()
        ->map(function (array $line) use ($directions) {
            $mapped = [
                'inventory_item_id' => (string) ($line['inventory_item_id'] ?? ''),
                'quantity' => (string) ($line['quantity'] ?? ''),
                'unit_cost' => (string) ($line['unit_cost'] ?? ''),
            ];

            if ($directions !== []) {
                $mapped['direction'] = (string) ($line['direction'] ?? ($directions[0]->value ?? ''));
            }

            return $mapped;
        })
        ->all();

    if ($initialLines === []) {
        $initialLines = [$defaultLine];
    }

    $lineGridClass = $directions === []
        ? 'grid grid-cols-[minmax(0,1fr)_8rem_8rem_2rem] gap-2'
        : 'grid grid-cols-[minmax(0,1fr)_8rem_8rem_8rem_2rem] gap-2';
@endphp

<h3 class="font-medium mt-4">{{ __('Lines') }}</h3>

@if ($dynamic)
    <div
        class="space-y-2"
        x-data="{
            lines: @js($initialLines),
            addLine() {
                this.lines.push(@js($defaultLine));
            },
            removeLine(index) {
                if (this.lines.length > 1) {
                    this.lines.splice(index, 1);
                }
            },
        }"
    >
        <div class="{{ $lineGridClass }} text-sm font-medium text-slate-500">
            @if (($fields['inventory_item_id']['visible'] ?? true))<span>{{ $fields['inventory_item_id']['label'] ?? __('Item') }}</span>@endif
            @if (($fields['quantity']['visible'] ?? true))<span>{{ $fields['quantity']['label'] ?? __('Qty') }}</span>@endif
            @if (($fields['unit_cost']['visible'] ?? true))<span>{{ $fields['unit_cost']['label'] ?? __('Unit cost') }}</span>@endif
            @if ($directions !== [])<span>{{ __('Direction') }}</span>@endif
            <span class="sr-only">{{ __('Actions') }}</span>
        </div>

        <template x-for="(line, index) in lines" :key="index">
            <div class="{{ $lineGridClass }}">
                @if (($fields['inventory_item_id']['visible'] ?? true))
                    <select
                        :name="`items[${index}][inventory_item_id]`"
                        class="erp-input w-full min-w-0"
                        x-model="line.inventory_item_id"
                        @if ($fields['inventory_item_id']['required'] ?? false) required @endif
                    >
                        <option value="">{{ __('—') }}</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->sku }} — {{ $item->item_name }}</option>
                        @endforeach
                    </select>
                @endif
                @if (($fields['quantity']['visible'] ?? true))
                    <input
                        type="number"
                        step="0.001"
                        min="0.001"
                        :name="`items[${index}][quantity]`"
                        class="erp-input w-full"
                        x-model="line.quantity"
                        placeholder="0"
                        @if ($fields['quantity']['required'] ?? false) required @endif
                    >
                @endif
                @if (($fields['unit_cost']['visible'] ?? true))
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        :name="`items[${index}][unit_cost]`"
                        class="erp-input w-full"
                        x-model="line.unit_cost"
                        placeholder="0"
                        @if ($fields['unit_cost']['required'] ?? false) required @endif
                    >
                @endif
                @if ($directions !== [])
                    <select :name="`items[${index}][direction]`" class="erp-input w-full" x-model="line.direction">
                        @foreach ($directions as $d)
                            <option value="{{ $d->value }}">{{ $d->value }}</option>
                        @endforeach
                    </select>
                @endif
                <div class="flex items-center justify-end">
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-sm text-rose-600 hover:bg-rose-50"
                        x-on:click="removeLine(index)"
                        x-show="lines.length > 1"
                        :title="@js(__('Remove line'))"
                    >
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">{{ __('Remove line') }}</span>
                    </button>
                </div>
            </div>
        </template>

        <button type="button" class="erp-btn-secondary text-xs" x-on:click="addLine()">{{ __('Add line') }}</button>
    </div>
@else
    <div class="space-y-2">
        <div class="grid grid-cols-4 gap-2 text-sm font-medium text-slate-500">
            @if (($fields['inventory_item_id']['visible'] ?? true))<span>{{ $fields['inventory_item_id']['label'] ?? __('Item') }}</span>@endif
            @if (($fields['quantity']['visible'] ?? true))<span>{{ $fields['quantity']['label'] ?? __('Qty') }}</span>@endif
            @if (($fields['unit_cost']['visible'] ?? true))<span>{{ $fields['unit_cost']['label'] ?? __('Unit cost') }}</span>@endif
            @if ($directions !== [])<span>{{ __('Direction') }}</span>@endif
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
                @if ($directions !== [])
                    <select name="items[{{ $i }}][direction]" class="erp-input">
                        @foreach ($directions as $d)<option value="{{ $d->value }}">{{ $d->value }}</option>@endforeach
                    </select>
                @endif
            </div>
        @endfor
    </div>
@endif
