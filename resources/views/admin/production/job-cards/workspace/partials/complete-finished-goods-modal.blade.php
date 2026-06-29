@php
    use App\Enums\InventoryStockRole;
    $suggestedId = old('finished_inventory_item_id', $completion['suggested_finished_item_id'] ?? null);
    $suggestedQty = old('quantity_completed', $completion['suggested_quantity_completed'] ?? 1);
    $suggestedNotes = old('notes', $completion['suggested_notes'] ?? '');
@endphp

<dialog id="complete-fg-modal" class="erp-modal w-full max-w-lg rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
    <form method="POST" action="{{ route('admin.production.job-cards.outputs.store', $jobCard) }}" class="p-5">
        @csrf
        <h3 class="text-base font-semibold text-slate-900">{{ __('Complete to finished goods') }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ __('Post completed output to the Finished Goods virtual warehouse. Accounting: Dr FG / Cr WIP (WIP was built from job material consumption).') }}</p>

        @if (! ($completion['eligible'] ?? false) && ! empty($completion['blockers'] ?? []))
            <ul class="mt-3 space-y-1 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                @foreach ($completion['blockers'] as $blocker)
                    <li>{{ $blocker }}</li>
                @endforeach
            </ul>
        @endif

        <div class="mt-4 space-y-3">
            <div>
                <label class="erp-label">{{ __('Finished item') }}</label>
                <select name="finished_inventory_item_id" class="erp-select w-full" @if ($suggestedId) required @endif>
                    @unless ($suggestedId)
                        <option value="">{{ __('Use BOM / sales order product') }}</option>
                    @endunless
                    @foreach ($finishedItems as $item)
                        @php
                            $needsRole = ($item->stock_role ?? null) !== InventoryStockRole::FinishedGood;
                        @endphp
                        <option value="{{ $item->id }}" @selected((string) $suggestedId === (string) $item->id)>
                            {{ $item->sku }} — {{ $item->item_name }}@if ($needsRole) ({{ __('set stock role to Finished Good') }})@endif
                        </option>
                    @endforeach
                </select>
                @if ($finishedItems->isEmpty())
                    <p class="mt-1 text-xs text-slate-500">{{ __('No finished-good catalogue items yet. Create one in Inventory or update the sales order product stock role.') }}</p>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="erp-label">{{ __('Quantity completed') }}</label>
                    <input type="number" step="0.001" min="0.001" name="quantity_completed" class="erp-input w-full" value="{{ $suggestedQty }}" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Quantity rejected') }}</label>
                    <input type="number" step="0.001" min="0" name="quantity_rejected" class="erp-input w-full" value="{{ old('quantity_rejected', 0) }}">
                </div>
            </div>
            <div>
                <label class="erp-label">{{ __('Unit cost') }}</label>
                @can('production.outputs.manual-cost')
                    <input type="number" step="0.0001" min="0" name="unit_cost" class="erp-input w-full" value="{{ old('unit_cost', $completion['suggested_unit_cost'] ?? '') }}" placeholder="{{ __('Leave blank to use job cost') }}">
                @else
                    <input type="text" class="erp-input w-full bg-slate-50" readonly value="{{ isset($completion['suggested_unit_cost']) ? number_format($completion['suggested_unit_cost'], 4) : __('Derived from job costing') }}">
                @endcan
            </div>
            <div>
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" rows="2" class="erp-input w-full">{{ $suggestedNotes }}</textarea>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" class="erp-btn-secondary" data-close-dialog="complete-fg-modal">{{ __('Cancel') }}</button>
            <button type="submit" class="erp-btn-primary">{{ __('Post to finished goods') }}</button>
        </div>
    </form>
</dialog>

<script>
    document.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.openDialog);
            dialog?.showModal();
        });
    });
    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.closeDialog);
            dialog?.close();
        });
    });
</script>
