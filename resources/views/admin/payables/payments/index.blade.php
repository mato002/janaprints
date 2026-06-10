<x-admin-layout :title="__('Supplier payments')">
    <x-admin.page-header :title="__('Supplier payments')">
        @can('create', App\Models\Procurement\SupplierPayment::class)
            <x-slot name="actions"><a href="{{ route('admin.payables.payments.create') }}" class="erp-btn-primary">{{ __('New payment') }}</a></x-slot>
        @endcan
    </x-admin.page-header>
    <x-admin.data-table
        export-route="admin.accounting.exports"
        :export-route-params="['listing' => 'supplier-payments']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="supplier-payments"
    >
        <x-slot name="head"><tr><th>{{ __('Number') }}</th><th>{{ __('Supplier') }}</th><th>{{ __('Date') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($payments as $payment)
                <tr>
                    <td><a href="{{ route('admin.payables.payments.show', $payment) }}" class="font-mono text-erp-accent">{{ $payment->payment_number }}</a></td>
                    <td>{{ $payment->vendor?->vendor_name }}</td>
                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                    <td class="font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->status->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state :title="__('No payments yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$payments" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
