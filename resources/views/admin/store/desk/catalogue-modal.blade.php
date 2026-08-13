<x-admin.modal-form :title="__('Catalogue')" maxWidth="5xl">
    <div class="space-y-4">
        <p class="text-sm text-slate-600">{{ __('Browse inventory items with operational stock status — without leaving the store desk.') }}</p>

        <form method="GET" action="{{ route('admin.store.desk.catalogue') }}" class="flex flex-wrap items-center gap-2">
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                class="erp-input min-w-[14rem] flex-1"
                placeholder="{{ __('Search SKU or item name…') }}"
            >
            <button type="submit" class="erp-btn-secondary text-sm">{{ __('Search') }}</button>
            @if ($search !== '')
                <a href="{{ route('admin.store.desk.catalogue') }}" class="erp-btn-ghost text-sm">{{ __('Clear') }}</a>
            @endif
        </form>

        <div class="overflow-x-auto rounded-lg border border-erp-border">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th class="text-right">{{ __('Available') }}</th>
                        <th class="text-right">{{ __('Reserved') }}</th>
                        <th>{{ __('Warehouse') }}</th>
                        <th>{{ __('Shelf') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php($item = $row['item'])
                        <tr>
                            <td class="font-medium">
                                <a href="{{ route('admin.inventory.items.show', $item) }}" class="text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ $item->item_name }}</a>
                            </td>
                            <td class="font-mono text-xs">{{ $item->sku }}</td>
                            <td class="text-right font-mono text-xs tabular-nums">{{ number_format($row['available'], 2) }}</td>
                            <td class="text-right font-mono text-xs tabular-nums">{{ number_format($row['reserved'], 2) }}</td>
                            <td>{{ $row['warehouse'] ?? '—' }}</td>
                            <td>{{ $row['shelf'] ?? '—' }}</td>
                            <td>{{ $item->category?->name ?? '—' }}</td>
                            <td>
                                @if ($item->stock_role)
                                    <span class="erp-badge {{ $item->stock_role->badgeClass() }}">{{ $item->stock_role->label() }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap items-center gap-2">
                                    @include('admin.inventory.items.partials.set-finished-good-form', [
                                        'item' => $item,
                                        'from' => 'store-desk',
                                        'buttonClass' => 'erp-btn-ghost py-1 text-xs',
                                    ])
                                    <a href="{{ route('admin.inventory.items.show', $item) }}" class="text-xs font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ __('View') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-sm text-slate-500">{{ __('No items match your search.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="text-sm">{{ $items->links() }}</div>
        @endif
    </div>
</x-admin.modal-form>
