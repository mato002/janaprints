<x-admin-layout :title="__('Credit notes')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Credit notes')]]">
    <x-admin.page-header :title="__('Credit notes')" :description="__('Customer credit notes issued against posted invoices.')" />

    <x-admin.data-table
        :search-placeholder="__('Search credit notes…')"
        export-route="admin.accounting.exports"
        :export-route-params="['listing' => 'customer-credit-notes']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="customer-credit-notes"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Number') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Credits invoice') }}</th>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col">{{ __('Total') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($creditNotes as $creditNote)
                <tr x-show="rowVisible(@js(strtolower($creditNote->invoice_number.' '.($creditNote->customer?->company_name ?? '').' '.($creditNote->creditedInvoice?->invoice_number ?? '').' '.$creditNote->status->value)))">
                    <td>
                        <a href="{{ route('admin.invoices.show', $creditNote) }}" class="font-mono text-sm text-erp-accent" data-turbo-frame="erp-main" data-turbo-action="advance">{{ $creditNote->invoice_number }}</a>
                    </td>
                    <td class="text-sm">{{ $creditNote->customer?->company_name }}</td>
                    <td class="text-sm font-mono">
                        @if ($creditNote->creditedInvoice)
                            <a href="{{ route('admin.invoices.show', $creditNote->creditedInvoice) }}" class="text-erp-accent hover:underline">{{ $creditNote->creditedInvoice->invoice_number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-sm">{{ $creditNote->invoice_date->format('Y-m-d') }}</td>
                    <td class="text-sm font-mono">{{ number_format($creditNote->total_amount, 2) }}</td>
                    <td>
                        <x-admin.status-badge :variant="match($creditNote->status) {
                            App\Enums\CustomerInvoiceStatus::Draft => 'neutral',
                            App\Enums\CustomerInvoiceStatus::Approved => 'info',
                            App\Enums\CustomerInvoiceStatus::Posted => 'success',
                            App\Enums\CustomerInvoiceStatus::Cancelled => 'warning',
                        }">{{ $creditNote->status->label() }}</x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.invoices.show', $creditNote)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="receipt-tax" :title="__('No credit notes yet')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $creditNotes->links() }}</div>
</x-admin-layout>
