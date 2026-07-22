<x-admin.modal-form :title="__('Reorder alerts')" maxWidth="5xl">
    <div class="space-y-4">
        <p class="text-sm text-slate-600">{{ __('Open low-stock alerts — acknowledge or resolve from this modal.') }}</p>

        <form method="GET" action="{{ route('admin.store.desk.reorder-alerts') }}" class="flex flex-wrap items-center gap-2">
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                class="erp-input min-w-[14rem] flex-1"
                placeholder="{{ __('Search SKU or item name…') }}"
            >
            <button type="submit" class="erp-btn-secondary text-sm">{{ __('Search') }}</button>
            @if ($search !== '')
                <a href="{{ route('admin.store.desk.reorder-alerts') }}" class="erp-btn-ghost text-sm">{{ __('Clear') }}</a>
            @endif
        </form>

        <div class="overflow-x-auto rounded-lg border border-erp-border">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('Warehouse') }}</th>
                        <th class="text-right">{{ __('Current') }}</th>
                        <th class="text-right">{{ __('Reorder') }}</th>
                        <th class="text-right">{{ __('Shortage') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alerts as $alert)
                        <tr>
                            <td>
                                <span class="font-medium">{{ $alert->inventoryItem?->item_name ?? '—' }}</span>
                                <span class="block font-mono text-[11px] text-slate-500">{{ $alert->inventoryItem?->sku }}</span>
                            </td>
                            <td>{{ $alert->warehouse?->name ?? '—' }}</td>
                            <td class="text-right font-mono text-xs">{{ number_format((float) $alert->current_quantity, 2) }}</td>
                            <td class="text-right font-mono text-xs">{{ number_format((float) $alert->reorder_level, 2) }}</td>
                            <td class="text-right font-mono text-xs">{{ number_format($alert->shortageQuantity(), 2) }}</td>
                            <td><x-admin.enum-status-badge :status="$alert->status->value" /></td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @can('acknowledge', $alert)
                                        <form method="POST" action="{{ route('admin.inventory.alerts.acknowledge', $alert) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="from" value="store-desk">
                                            <button type="submit" class="erp-btn-secondary text-xs">{{ __('Acknowledge') }}</button>
                                        </form>
                                    @endcan
                                    @can('resolve', $alert)
                                        <form method="POST" action="{{ route('admin.inventory.alerts.resolve', $alert) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="from" value="store-desk">
                                            <button type="submit" class="erp-btn-secondary text-xs">{{ __('Resolve') }}</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-sm text-slate-500">{{ __('No open reorder alerts.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($alerts->hasPages())
            <div class="text-sm">{{ $alerts->links() }}</div>
        @endif
    </div>
</x-admin.modal-form>
