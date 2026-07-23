<x-admin-layout :title="__('Receipts')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Desk'), 'url' => route('admin.store.desk')], ['label' => __('Receipts')]]">
    @unless (\App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext())
        @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => \App\Support\Inventory\StoreDeskViews::RECEIPTS])
    @endunless
    <x-admin.page-header :title="__('Stock receipts')">
        @can('create', App\Models\Inventory\StockReceipt::class)
            <a href="{{ route('admin.inventory.receipts.create') }}" class="erp-btn-primary">{{ __('New receipt') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search receipts…')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'stock-receipts']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="stock-receipts"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Receipt') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($receipts as $r)
                <tr x-show="rowVisible(@js(strtolower($r->receipt_number.' '.$r->status->value)))">
                    <td class="font-medium">{{ $r->receipt_number }}</td>
                    <td><x-admin.enum-status-badge :status="$r->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.receipts.show', $r)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3"><x-admin.empty-state icon="archive" :title="__('No receipts yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$receipts" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
