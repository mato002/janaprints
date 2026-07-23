<x-admin-layout :title="__('Supplier Quotations')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')], ['label' => __('Supplier Quotations')]]">
    @include('admin.procurement.partials.desk-mode-nav', ['activeProcurementView' => \App\Support\Procurement\ProcurementDeskViews::RFQS])
    <x-admin.page-header :title="__('Supplier Quotations')">
        <x-slot name="actions">
            @can('create', App\Models\Procurement\SupplierQuotation::class)
                <a href="{{ route('admin.procurement.quotations.create') }}" class="erp-btn-primary">{{ __('Create quotation') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search supplier quotations…')"
        export-route="admin.procurement.exports"
        :export-route-params="['listing' => 'supplier-quotations']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="supplier-quotations"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Number') }}</th>
                <th scope="col">{{ __('Vendor') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Date') }}</th>
                <th scope="col">{{ __('Total') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($quotations as $quotation)
                <tr x-show="rowVisible(@js(strtolower($quotation->quotation_number.' '.($quotation->vendor?->vendor_name ?? '').' '.$quotation->status->value)))">
                    <td class="font-mono text-xs">{{ $quotation->quotation_number }}</td>
                    <td>{{ $quotation->vendor?->vendor_name ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $quotation->quotation_date?->format('Y-m-d') }}</td>
                    <td class="tabular-nums">{{ number_format($quotation->total_amount, 2) }}</td>
                    <td><x-admin.enum-status-badge :status="$quotation->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.procurement.quotations.show', $quotation)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="document-text" :title="__('No supplier quotations')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$quotations" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
