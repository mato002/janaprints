@php
    use App\Enums\InventoryStockRole;

    $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    $eligible = (bool) ($completion['eligible'] ?? false);
    $blockers = $completion['blockers'] ?? [];
    $remaining = (int) ($workflowPresentation['readiness_remaining_count'] ?? count($blockers));
    $suggestedId = old('finished_inventory_item_id', $completion['suggested_finished_item_id'] ?? null);
    $suggestedQty = old('quantity_completed', $completion['suggested_quantity_completed'] ?? 1);
    $suggestedItem = ($finishedItems ?? collect())->firstWhere('id', $suggestedId) ?? $jobCard->inventoryItem;
    $qtyLabel = number_format((float) $suggestedQty, 0);
    $postLabel = __('Post :qty finished goods', ['qty' => $qtyLabel]);
    $warehouseLabel = ($completion['fg_warehouse']['code'] ?? null)
        ? ($completion['fg_warehouse']['code'].' — '.$completion['fg_warehouse']['name'])
        : __('Finished goods virtual warehouse');
    $needsRole = $suggestedItem && ($suggestedItem->stock_role ?? null) !== InventoryStockRole::FinishedGood;
@endphp

<x-admin.card>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Finished goods output') }}</h3>
            <p class="text-sm text-slate-600">{{ __('Post completed production into finished goods inventory when all requirements are met.') }}</p>
        </div>

        @can('production.outputs.post')
            <div class="flex flex-wrap items-center gap-2">
                @if ($eligible)
                    <button type="button" class="erp-btn-primary text-sm" data-open-dialog="complete-fg-modal">{{ $postLabel }}</button>
                @else
                    <button type="button" class="erp-btn-primary text-sm opacity-60" disabled>{{ __('Post to finished goods') }}</button>
                    @if ($remaining > 0)
                        <span class="text-xs font-medium text-amber-700">{{ trans_choice(':count requirement remaining|:count requirements remaining', $remaining, ['count' => $remaining]) }}</span>
                    @endif
                @endif
            </div>
        @endcan
    </div>

    <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
        <div class="rounded-md border border-erp-border bg-slate-50 px-3 py-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Finished item') }}</dt>
            <dd class="mt-1 text-slate-800">
                @if ($suggestedItem)
                    <span class="font-mono text-xs text-slate-500">{{ $suggestedItem->sku }}</span><br>
                    {{ $suggestedItem->item_name }}
                    @if ($needsRole)
                        <span class="mt-1 block text-xs text-amber-700">{{ __('Set stock role to Finished Good') }}</span>
                    @endif
                @else
                    {{ __('Not resolved yet') }}
                @endif
            </dd>
        </div>
        <div class="rounded-md border border-erp-border bg-slate-50 px-3 py-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Completed qty') }}</dt>
            <dd class="mt-1 tabular-nums text-slate-800">{{ $qtyLabel }}</dd>
        </div>
        <div class="rounded-md border border-erp-border bg-slate-50 px-3 py-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Warehouse') }}</dt>
            <dd class="mt-1 text-slate-800">{{ $warehouseLabel }}</dd>
        </div>
    </dl>
</x-admin.card>

@if (auth()->user()?->can('production.outputs.view'))
    <div class="mt-4">
        <a href="{{ route('admin.production.outputs.index') }}" class="erp-link text-sm">{{ __('All outputs') }}</a>
    </div>
@endif
