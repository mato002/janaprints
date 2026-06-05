<x-admin-layout :title="__('Price Books')" :breadcrumbs="[['label' => __('Commercial')], ['label' => __('Price Books')]]">
    <x-admin.page-header :title="__('Commercial price books')" :description="__('Customer-facing selling rules for quotations and POS.')">
        <x-slot name="actions">
            @can('create', App\Models\Commercial\CommercialPriceBook::class)
                <a href="{{ route('admin.commercial.price-books.create') }}" class="erp-btn-primary">{{ __('New price book') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search price books...')" export-filename="price-books">
        <x-slot name="head">
            <th>{{ __('Name') }}</th>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Default') }}</th>
            <th class="erp-table-actions-col">{{ __('Actions') }}</th>
        </x-slot>
        <x-slot name="body">
            @forelse ($priceBooks as $book)
                <tr>
                    <td class="font-medium">{{ $book->name }}</td>
                    <td class="font-mono text-sm">{{ $book->code }}</td>
                    <td>{{ $book->branch?->name ?? __('Company-wide') }}</td>
                    <td><x-admin.status-badge :variant="$book->status === App\Enums\CommercialPriceBookStatus::Active ? 'success' : 'neutral'">{{ $book->status->label() }}</x-admin.status-badge></td>
                    <td>{{ $book->is_default ? __('Yes') : __('No') }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.commercial.price-books.show', $book)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $book)
                                <x-admin.table-row-action :href="route('admin.commercial.price-books.edit', $book)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No price books yet.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $priceBooks->links() }}</div>
</x-admin-layout>
