@php
    $bom = $bom ?? null;
    $suggestedLines = $suggestedLines ?? null;
    $defaultLines = $suggestedLines ?: [['inventory_item_id' => '', 'quantity_per_unit' => '', 'waste_factor_percent' => 0, 'notes' => '']];
    $lineRows = old('lines', $bom
        ? $bom->lines->map(fn ($line) => [
            'inventory_item_id' => (string) $line->inventory_item_id,
            'quantity_per_unit' => $line->quantity_per_unit,
            'waste_factor_percent' => $line->waste_factor_percent,
            'notes' => $line->notes,
        ])->all()
        : $defaultLines);
    $finishedItemValue = old('finished_item_id', $bom?->finished_item_id ?? $preselectedFinishedItemId ?? null);
    $nameValue = old('name', $bom?->name ?? $prefilledName ?? '');
    $materialOptions = collect($rawMaterials)->map(fn ($material) => [
        'id' => (string) $material->id,
        'label' => trim(($material->category?->name ? $material->category->name.' · ' : '').$material->sku.' — '.$material->item_name),
        'group' => $material->category?->name ?: ($material->stock_role?->label() ?? __('Materials')),
    ])->values()->all();
    $hasSuggestions = ! $bom && collect($lineRows)->contains(fn ($line) => filled($line['inventory_item_id'] ?? null));
@endphp

<div class="space-y-4" x-data="bomFormLines(@js($lineRows), @js($materialOptions))">
    <x-admin.card>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="erp-label">{{ __('Finished product') }}</label>
                <select name="finished_item_id" class="erp-input" required @disabled($bom)>
                    <option value="">{{ __('Select product') }}</option>
                    @foreach ($finishedItems as $item)
                        <option value="{{ $item->id }}" @selected((int) $finishedItemValue === $item->id)>{{ $item->sku }} — {{ $item->item_name }}</option>
                    @endforeach
                </select>
                @if ($bom)
                    <input type="hidden" name="finished_item_id" value="{{ $bom->finished_item_id }}">
                @endif
            </div>
            <div>
                <label class="erp-label">{{ __('BOM name') }}</label>
                <input type="text" name="name" class="erp-input" value="{{ $nameValue }}" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Version') }}</label>
                <input type="number" name="version" min="1" class="erp-input" value="{{ old('version', $bom?->version ?? 1) }}">
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded" @checked(old('is_active', $bom?->is_active ?? true))>
                    {{ __('Active') }}
                </label>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="erp-input" rows="2">{{ old('notes', $bom?->notes ?? '') }}</textarea>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Raw materials') }}</h3>
            <button type="button" class="erp-btn-secondary text-sm" @click="addLine()">{{ __('Add line') }}</button>
        </div>
        <p class="mb-3 text-sm text-slate-600">
            {{ __('Pick paper, ink, and finishing this job will consume — not the finished product. Qty / unit is how much of that material one finished piece uses (for example 0.5 sheets of paper per flyer). Generate requirements next to scale by job quantity.') }}
        </p>
        @if ($hasSuggestions)
            <p class="mb-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-950">
                {{ __('Lines below are suggested from the job specification and typical print stock. Change or remove anything that does not apply.') }}
            </p>
        @endif

        <div class="space-y-3">
            <template x-for="(line, index) in lines" :key="index">
                <div class="grid grid-cols-1 gap-2 rounded border border-slate-200 p-3 md:grid-cols-5">
                    <div class="md:col-span-2">
                        <label class="erp-label">{{ __('Material') }}</label>
                        <select class="erp-input" :name="`lines[${index}][inventory_item_id]`" x-model="line.inventory_item_id" required>
                            <option value="">{{ __('Select material') }}</option>
                            <template x-for="material in materials" :key="material.id">
                                <option :value="material.id" x-text="material.label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Qty / unit') }}</label>
                        <input type="number" step="0.0001" min="0.0001" class="erp-input" :name="`lines[${index}][quantity_per_unit]`" x-model="line.quantity_per_unit" required>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Waste %') }}</label>
                        <input type="number" step="0.01" min="0" max="100" class="erp-input" :name="`lines[${index}][waste_factor_percent]`" x-model="line.waste_factor_percent">
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Notes') }}</label>
                        <div class="flex gap-2">
                            <input type="text" class="erp-input" :name="`lines[${index}][notes]`" x-model="line.notes">
                            <button type="button" class="erp-btn-ghost text-xs text-red-600" x-show="lines.length > 1" @click="removeLine(index)">{{ __('Remove') }}</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </x-admin.card>
</div>
