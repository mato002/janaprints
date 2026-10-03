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

    $itemCatalog = collect($items ?? [])->map(fn ($item) => [
        'id' => (string) $item->id,
        'label' => trim($item->sku.' — '.$item->item_name),
    ])->values()->all();

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
            catalog: @js($itemCatalog),
            defaultLine: @js($defaultLine),
            pickerOpen: false,
            pickerQuery: '',
            addLine() {
                this.lines.push({ ...this.defaultLine });
            },
            removeLine(index) {
                if (this.lines.length > 1) {
                    this.lines.splice(index, 1);
                } else {
                    this.lines = [{ ...this.defaultLine }];
                }
            },
            itemLabel(id) {
                const match = this.catalog.find((item) => String(item.id) === String(id));
                return match ? match.label : '';
            },
            hasItem(id) {
                return this.lines.some((line) => String(line.inventory_item_id) === String(id));
            },
            selectedCount() {
                return this.lines.filter((line) => line.inventory_item_id).length;
            },
            filteredItems() {
                const query = (this.pickerQuery || '').toLowerCase().trim();
                if (! query) {
                    return this.catalog;
                }

                return this.catalog.filter((item) => item.label.toLowerCase().includes(query));
            },
            ensureLine(id) {
                const itemId = String(id);
                if (this.hasItem(itemId)) {
                    return;
                }

                const emptyIndex = this.lines.findIndex((line) => ! line.inventory_item_id);
                const next = { ...this.defaultLine, inventory_item_id: itemId };

                if (emptyIndex >= 0) {
                    this.lines.splice(emptyIndex, 1, { ...this.lines[emptyIndex], inventory_item_id: itemId });
                    return;
                }

                this.lines.push(next);
            },
            removeItem(id) {
                const itemId = String(id);
                this.lines = this.lines.filter((line) => String(line.inventory_item_id) !== itemId);
                if (this.lines.length === 0) {
                    this.lines = [{ ...this.defaultLine }];
                }
            },
            toggleItem(id) {
                if (this.hasItem(id)) {
                    this.removeItem(id);
                    return;
                }

                this.ensureLine(id);
            },
            addFiltered() {
                this.filteredItems().forEach((item) => this.ensureLine(item.id));
            },
        }"
    >
        @if (($fields['inventory_item_id']['visible'] ?? true))
            <div class="rounded-md border border-erp-border bg-erp-page/40">
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm"
                    x-on:click="pickerOpen = ! pickerOpen"
                >
                    <span>
                        <span class="font-medium text-slate-800">{{ __('Select items') }}</span>
                        <span class="mt-0.5 block text-xs font-normal text-slate-500">{{ __('Tick several products to add lines, then enter quantity and unit cost.') }}</span>
                    </span>
                    <span class="shrink-0 text-xs tabular-nums text-slate-500" x-text="selectedCount() ? selectedCount() + ' ' + @js(__('selected')) : @js(__('None selected'))"></span>
                </button>
                <div class="border-t border-erp-border px-3 py-2" x-show="pickerOpen" x-cloak>
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <input
                            type="search"
                            class="erp-input min-w-[12rem] flex-1 text-sm"
                            x-model="pickerQuery"
                            placeholder="{{ __('Search SKU or name…') }}"
                        >
                        <button type="button" class="erp-btn-secondary text-xs" x-on:click="addFiltered()">{{ __('Add all matching') }}</button>
                    </div>
                    <div class="max-h-52 space-y-1 overflow-y-auto pr-1">
                        <template x-for="item in filteredItems()" :key="item.id">
                            <label class="flex cursor-pointer items-start gap-2 rounded-md px-1 py-1 text-sm hover:bg-white">
                                <input
                                    type="checkbox"
                                    class="mt-1"
                                    :checked="hasItem(item.id)"
                                    x-on:change="toggleItem(item.id)"
                                >
                                <span x-text="item.label"></span>
                            </label>
                        </template>
                        <p class="px-1 py-2 text-xs text-slate-500" x-show="filteredItems().length === 0">{{ __('No matching items.') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="{{ $lineGridClass }} text-sm font-medium text-slate-500">
            @if (($fields['inventory_item_id']['visible'] ?? true))<span>{{ $fields['inventory_item_id']['label'] ?? __('Item') }}</span>@endif
            @if (($fields['quantity']['visible'] ?? true))<span>{{ $fields['quantity']['label'] ?? __('Qty') }}</span>@endif
            @if (($fields['unit_cost']['visible'] ?? true))<span>{{ $fields['unit_cost']['label'] ?? __('Unit cost') }}</span>@endif
            @if ($directions !== [])<span>{{ __('Direction') }}</span>@endif
            <span class="sr-only">{{ __('Actions') }}</span>
        </div>

        <template x-for="(line, index) in lines" :key="line.inventory_item_id ? ('item-'+line.inventory_item_id) : ('empty-'+index)">
            <div class="{{ $lineGridClass }}">
                @if (($fields['inventory_item_id']['visible'] ?? true))
                    <div>
                        <input type="hidden" :name="`items[${index}][inventory_item_id]`" :value="line.inventory_item_id">
                        <p
                            class="erp-input flex min-h-[2.5rem] items-center text-sm"
                            x-show="line.inventory_item_id"
                            x-text="itemLabel(line.inventory_item_id)"
                        ></p>
                        <select
                            class="erp-input w-full min-w-0"
                            x-show="! line.inventory_item_id"
                            x-model="line.inventory_item_id"
                            @if ($fields['inventory_item_id']['required'] ?? false) :required="! line.inventory_item_id && selectedCount() === 0" @endif
                        >
                            <option value="">{{ __('—') }}</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">{{ $item->sku }} — {{ $item->item_name }}</option>
                            @endforeach
                        </select>
                    </div>
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
                        x-show="lines.length > 1 || line.inventory_item_id"
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
