<x-admin-layout :title="$payment->payment_number">
    <x-admin.page-header :title="$payment->payment_number" :description="$payment->vendor?->vendor_name" />
    <x-admin.card class="mb-4">
        @can('post', $payment)
            <form method="POST" action="{{ route('admin.payables.payments.post', $payment) }}">@csrf<button class="erp-btn-primary">{{ __('Post payment') }}</button></form>
        @endcan
    </x-admin.card>
    <x-admin.kpi-widget :label="__('Amount')" :value="number_format($payment->amount, 2)" />
    <x-admin.card class="mt-4">
        <h3 class="font-medium mb-2">{{ __('Allocations') }}</h3>
        @foreach ($payment->allocations as $allocation)
            <div class="flex justify-between text-sm border-t border-erp-border py-2">
                <a href="{{ route('admin.payables.bills.show', $allocation->bill) }}" class="text-erp-accent">{{ $allocation->bill->bill_number }}</a>
                <span class="font-mono">{{ number_format($allocation->amount, 2) }}</span>
            </div>
        @endforeach
    </x-admin.card>
</x-admin-layout>
