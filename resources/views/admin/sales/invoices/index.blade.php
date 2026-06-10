<x-admin-layout :title="__('Customer invoices')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Invoices')]]">
    <x-admin.page-header :title="__('Customer invoices')" :description="__('Draft, approve, and post invoices to accounts receivable.')" />

    <x-admin.data-table
        :search-placeholder="__('Search invoices…')"
        export-route="admin.accounting.exports"
        :export-route-params="['listing' => 'customer-invoices']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="customer-invoices"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Number') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Total') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($invoices as $invoice)
                <tr x-show="rowVisible(@js(strtolower($invoice->invoice_number.' '.($invoice->customer?->company_name ?? '').' '.$invoice->status->value)))">
                    <td>
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="font-mono text-sm text-erp-accent">{{ $invoice->invoice_number }}</a>
                    </td>
                    <td class="text-sm">{{ $invoice->customer?->company_name }}</td>
                    <td class="text-sm">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    <td class="text-sm">{{ $invoice->invoice_type->label() }}</td>
                    <td class="text-sm font-mono">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td>
                        <x-admin.status-badge :variant="match($invoice->status) {
                            App\Enums\CustomerInvoiceStatus::Draft => 'neutral',
                            App\Enums\CustomerInvoiceStatus::Approved => 'info',
                            App\Enums\CustomerInvoiceStatus::Posted => 'success',
                            App\Enums\CustomerInvoiceStatus::Cancelled => 'warning',
                        }">{{ $invoice->status->label() }}</x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.invoices.show', $invoice)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="receipt-tax" :title="__('No invoices yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$invoices" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
