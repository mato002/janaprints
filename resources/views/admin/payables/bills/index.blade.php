<x-admin-layout :title="__('Supplier bills')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Supplier bills')]]">
    <x-admin.page-header :title="__('Supplier bills')" :description="__('Draft, approve, post, and pay supplier obligations.')">
        @can('create', App\Models\Procurement\SupplierBill::class)
            <x-slot name="actions">
                <a href="{{ route('admin.payables.bills.create') }}" class="erp-btn-primary">{{ __('New bill') }}</a>
            </x-slot>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Number') }}</th>
                <th>{{ __('Supplier') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Balance') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($bills as $bill)
                <tr>
                    <td><a href="{{ route('admin.payables.bills.show', $bill) }}" class="font-mono text-erp-accent">{{ $bill->bill_number }}</a></td>
                    <td>{{ $bill->vendor?->vendor_name }}</td>
                    <td>{{ $bill->bill_date->format('Y-m-d') }}</td>
                    <td class="font-mono">{{ number_format($bill->total_amount, 2) }}</td>
                    <td class="font-mono">{{ number_format($bill->balance_due, 2) }}</td>
                    <td>{{ $bill->status->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="receipt-tax" :title="__('No supplier bills yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$bills" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
