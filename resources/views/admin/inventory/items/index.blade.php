<x-admin-layout :title="__('Inventory items')" :breadcrumbs="[['label' => __('Inventory'), 'url' => route('admin.inventory.dashboard')], ['label' => __('Items')]]">
    <x-admin.page-header :title="__('Inventory items')">
        @can('create', App\Models\Inventory\InventoryItem::class)
            <a href="{{ route('admin.inventory.items.create') }}" class="erp-btn-primary">{{ __('New item') }}</a>
        @endcan
    </x-admin.page-header>
    <x-admin.card>
        <table class="erp-table w-full text-sm">
            <thead><tr><th>{{ __('SKU') }}</th><th>{{ __('Name') }}</th><th>{{ __('Category') }}</th><th>{{ __('Reorder') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->category?->name }}</td>
                        <td>{{ $item->reorder_level }}</td>
                        <td><a href="{{ route('admin.inventory.items.show', $item) }}" class="text-indigo-600">{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500 py-4">{{ __('No items.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $items->links() }}</div>
    </x-admin.card>
</x-admin-layout>
