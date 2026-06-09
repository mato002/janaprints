@php($bom = $bom ?? null)
@php($lineRows = old('lines', $bom ? $bom->lines->map(fn ($line) => [
    'inventory_item_id' => $line->inventory_item_id,
    'quantity_per_unit' => $line->quantity_per_unit,
    'waste_factor_percent' => $line->waste_factor_percent,
    'notes' => $line->notes,
])->all() : [['inventory_item_id' => '', 'quantity_per_unit' => '', 'waste_factor_percent' => 0, 'notes' => '']])))

<x-admin.card class="mb-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="erp-label">{{ __('Finished product') }}</label>
            <select name="finished_item_id" class="erp-input" required @disabled($bom)>
                <option value="">{{ __('Select product') }}</option>
                @foreach ($finishedItems as $item)
                    <option value="{{ $item->id }}" @selected((int) old('finished_item_id', $bom?->finished_item_id) === $item->id)>{{ $item->sku }} — {{ $item->item_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label">{{ __('BOM name') }}</label>
            <input type="text" name="name" class="erp-input" value="{{ old('name', $bom?->name ?? '') }}" required>
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
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Raw materials') }}</h3>
    <div class="space-y-3" id="bom-lines">
        @foreach ($lineRows as $index => $line)
            <div class="grid grid-cols-1 gap-2 rounded border border-slate-200 p-3 md:grid-cols-5">
                <div class="md:col-span-2">
                    <label class="erp-label">{{ __('Material') }}</label>
                    <select name="lines[{{ $index }}][inventory_item_id]" class="erp-input" required>
                        <option value="">{{ __('Select material') }}</option>
                        @foreach ($rawMaterials as $material)
                            <option value="{{ $material->id }}" @selected((int) ($line['inventory_item_id'] ?? 0) === $material->id)>{{ $material->sku }} — {{ $material->item_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Qty / unit') }}</label>
                    <input type="number" step="0.0001" min="0.0001" name="lines[{{ $index }}][quantity_per_unit]" class="erp-input" value="{{ $line['quantity_per_unit'] ?? '' }}" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Waste %') }}</label>
                    <input type="number" step="0.01" min="0" max="100" name="lines[{{ $index }}][waste_factor_percent]" class="erp-input" value="{{ $line['waste_factor_percent'] ?? 0 }}">
                </div>
                <div>
                    <label class="erp-label">{{ __('Notes') }}</label>
                    <input type="text" name="lines[{{ $index }}][notes]" class="erp-input" value="{{ $line['notes'] ?? '' }}">
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="erp-btn-secondary mt-3 text-sm" onclick="addBomLine()">{{ __('Add line') }}</button>
</x-admin.card>

<script>
    function addBomLine() {
        const container = document.getElementById('bom-lines');
        const index = container.children.length;
        const template = container.children[0]?.cloneNode(true);
        if (!template) return;
        template.querySelectorAll('[name]').forEach((input) => {
            input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
            if (input.tagName === 'SELECT') input.selectedIndex = 0;
            else input.value = input.name.includes('waste_factor_percent') ? '0' : '';
        });
        container.appendChild(template);
    }
</script>
