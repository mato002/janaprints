@php
    use App\Enums\InventoryStockRole;

    $eligible = (bool) ($completion['eligible'] ?? false);
    $suggestedId = old('finished_inventory_item_id', $completion['suggested_finished_item_id'] ?? null);
    $suggestedQty = old('quantity_completed', $completion['suggested_quantity_completed'] ?? 1);
    $suggestedNotes = old('notes', $completion['suggested_notes'] ?? '');
    $warehouseLabel = ($completion['fg_warehouse']['code'] ?? null)
        ? ($completion['fg_warehouse']['code'].' — '.$completion['fg_warehouse']['name'])
        : __('Finished goods virtual warehouse');
@endphp

<dialog id="complete-fg-modal" class="erp-modal job-360-fg-modal w-full max-w-xl rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
    <form method="POST" action="{{ route('admin.production.job-cards.outputs.store', $jobCard) }}" class="p-5">
        @csrf
        <h3 class="text-base font-semibold text-slate-900">{{ __('Post finished goods') }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ __('Confirm output details before posting to finished goods inventory.') }}</p>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="sm:col-span-2">
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
            </div>
            <div>
                <label class="erp-label">{{ __('Completed qty') }}</label>
                <input type="number" step="0.001" min="0.001" name="quantity_completed" class="erp-input w-full" value="{{ $suggestedQty }}" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Rejected qty') }}</label>
                <input type="number" step="0.001" min="0" name="quantity_rejected" class="erp-input w-full" value="{{ old('quantity_rejected', 0) }}">
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
                <label class="erp-label">{{ __('Warehouse') }}</label>
                <input type="text" class="erp-input w-full bg-slate-50" readonly value="{{ $warehouseLabel }}">
            </div>
            <div class="sm:col-span-2">
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" rows="2" class="erp-input w-full">{{ $suggestedNotes }}</textarea>
            </div>
        </div>

        @if ($eligible)
            <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">{{ __('Accounting impact') }}</p>
                <div class="mt-1 grid gap-1 text-sm text-emerald-900 sm:grid-cols-2">
                    <p>{{ __('Dr Finished goods inventory') }}</p>
                    <p>{{ __('Cr Work in progress') }}</p>
                </div>
            </div>
        @endif

        <div class="mt-5 flex flex-wrap justify-end gap-2">
            <button type="button" class="erp-btn-secondary" data-close-dialog="complete-fg-modal">{{ __('Cancel') }}</button>
            <button type="submit" class="erp-btn-primary" @disabled(! $eligible)>{{ __('Post finished goods') }}</button>
        </div>
    </form>
</dialog>
