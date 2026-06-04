<x-admin-layout :title="$payment->payment_number">
    <x-admin.page-header :title="$payment->payment_number" :description="$payment->customer?->company_name">
        <x-admin.status-badge :variant="match($payment->status) { App\Enums\CustomerPaymentStatus::Draft => 'neutral', App\Enums\CustomerPaymentStatus::Posted => 'success', App\Enums\CustomerPaymentStatus::Cancelled => 'warning' }">{{ $payment->status->label() }}</x-admin.status-badge>
        @can('update', $payment)<a href="{{ route('admin.payments.edit', $payment) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>@endcan
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.kpi-widget :label="__('Amount')" :value="number_format($payment->amount, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Allocated')" :value="number_format($payment->allocated_amount, 2)" icon="scale" />
        <x-admin.kpi-widget :label="__('Unallocated')" :value="number_format($payment->unallocated_amount, 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('Method')" :value="$payment->payment_method->label()" icon="credit-card" />
    </div>

    <x-admin.card class="mb-4">
        <div class="flex flex-wrap gap-2">
            @can('post', $payment)
                <form method="POST" action="{{ route('admin.payments.post', $payment) }}">@csrf<button class="erp-btn-primary">{{ __('Post to ledger') }}</button></form>
            @endcan
            @can('cancel', $payment)
                <form method="POST" action="{{ route('admin.payments.cancel', $payment) }}">@csrf<button class="erp-btn-secondary text-red-600">{{ __('Cancel') }}</button></form>
            @endcan
        </div>
    </x-admin.card>

    @if ($payment->allocations->isNotEmpty())
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-3">{{ __('Allocations') }}</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase text-slate-400"><th>{{ __('Invoice') }}</th><th>{{ __('Amount') }}</th></tr></thead>
                <tbody>
                    @foreach ($payment->allocations as $allocation)
                        <tr class="border-t border-erp-border">
                            <td class="py-2"><a href="{{ route('admin.invoices.show', $allocation->invoice) }}" class="text-erp-accent font-mono">{{ $allocation->invoice->invoice_number }}</a></td>
                            <td class="py-2 font-mono">{{ number_format($allocation->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @endif

    @if ($payment->postedJournal)
        <p class="text-sm">{{ __('GL') }}: <a href="{{ route('admin.accounting.journals.show', $payment->postedJournal) }}" class="text-erp-accent">{{ $payment->postedJournal->journal_number }}</a></p>
    @endif
</x-admin-layout>
