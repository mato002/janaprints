<x-admin-layout :title="__('Goods Receipts')" :breadcrumbs="[['label' => __('Procurement')], ['label' => __('Goods Receipts')]]">
    <x-admin.page-header :title="__('Goods Receipts')" />

    <x-admin.data-table :search-placeholder="__('Search goods receipts…')" export-filename="goods-receipts">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Number') }}</th>
                <th scope="col">{{ __('PO') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Vendor') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($receipts as $receipt)
                <tr x-show="rowVisible(@js(strtolower($receipt->receipt_number.' '.($receipt->purchaseOrder?->po_number ?? '').' '.($receipt->purchaseOrder?->vendor?->vendor_name ?? '').' '.$receipt->status->value)))">
                    <td class="font-mono text-xs">{{ $receipt->receipt_number }}</td>
                    <td>{{ $receipt->purchaseOrder?->po_number ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $receipt->purchaseOrder?->vendor?->vendor_name ?? '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$receipt->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.procurement.receipts.show', $receipt)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="archive" :title="__('No goods receipts')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$receipts" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
