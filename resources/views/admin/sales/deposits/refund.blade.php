<x-admin-layout :title="__('Refund deposit')">
    <x-admin.page-header :title="__('Refund customer deposit')" :description="$payment->payment_number.' · '.$payment->customer?->company_name">
        <a href="{{ route('admin.payments.show', $payment) }}" class="erp-btn-secondary">{{ __('Back to payment') }}</a>
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.kpi-widget :label="__('Credit issued')" :value="number_format($payment->credit_issued, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Credit applied')" :value="number_format($payment->credit_applied, 2)" icon="scale" />
        <x-admin.kpi-widget :label="__('Credit refunded')" :value="number_format($payment->credit_refunded, 2)" icon="receipt-tax" />
        <x-admin.kpi-widget :label="__('Remaining')" :value="number_format($payment->credit_remaining, 2)" icon="cash" />
    </div>

    <x-admin.card>
        <form method="POST" action="{{ route('admin.payments.refund-deposit.store', $payment) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Refund amount') }}</label>
                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $payment->credit_remaining }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Refund date') }}</label>
                <input type="date" name="refund_date" value="{{ now()->toDateString() }}" class="erp-input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Refund method') }}</label>
                <select name="payment_method" class="erp-input w-full">
                    @foreach (App\Enums\CustomerPaymentMethod::cases() as $method)
                        <option value="{{ $method->value }}" @selected($payment->payment_method === $method)>{{ $method->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Reference') }}</label>
                <input type="text" name="reference" class="erp-input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Notes') }}</label>
                <textarea name="notes" rows="2" class="erp-input w-full"></textarea>
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Post refund') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
