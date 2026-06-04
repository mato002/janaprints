<x-admin-layout :title="$quotation->quotation_number" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')], ['label' => __('Supplier Quotations'), 'url' => route('admin.procurement.quotations.index')], ['label' => $quotation->quotation_number]]">
    <x-admin.page-header :title="$quotation->quotation_number" :description="$quotation->vendor?->vendor_name" />
    <x-admin.card>
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Description') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Unit cost') }}</th><th>{{ __('Total') }}</th></tr></thead>
            <tbody>
                @foreach ($quotation->items as $item)
                    <tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_cost, 2) }}</td><td>{{ number_format($item->line_total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
