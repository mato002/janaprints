<x-admin-layout :title="__('Payments')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Payments')]]">
    <x-admin.page-header :title="__('Customer payments')">
        @can('create', App\Models\Sales\CustomerPayment::class)
            <a href="{{ route('admin.payments.create') }}" class="erp-btn-primary">{{ __('Record payment') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table
        export-route="admin.accounting.exports"
        :export-route-params="['listing' => 'customer-payments']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="customer-payments"
    >
        <x-slot name="head">
            <tr>
                <th>{{ __('Number') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Method') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($payments as $payment)
                <tr>
                    <td><a href="{{ route('admin.payments.show', $payment) }}" class="font-mono text-erp-accent">{{ $payment->payment_number }}</a></td>
                    <td>{{ $payment->customer?->company_name }}</td>
                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                    <td>{{ $payment->payment_method->label() }}@if($payment->is_deposit) <span class="erp-badge text-[10px]">{{ __('Deposit') }}</span>@endif</td>
                    <td class="font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td><x-admin.status-badge :variant="match($payment->status) { App\Enums\CustomerPaymentStatus::Draft => 'neutral', App\Enums\CustomerPaymentStatus::Posted => 'success', App\Enums\CustomerPaymentStatus::Cancelled => 'warning' }">{{ $payment->status->label() }}</x-admin.status-badge></td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="credit-card" :title="__('No payments yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$payments" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
