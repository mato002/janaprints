<x-admin-layout :title="__('Edit payment')">
    <x-admin.page-header :title="__('Edit :number', ['number' => $payment->payment_number])" />

    <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="max-w-3xl space-y-6">
        @csrf @method('PUT')
        <x-admin.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="erp-label">{{ __('Date') }}</label><input type="date" name="payment_date" value="{{ $payment->payment_date->toDateString() }}" class="erp-input w-full" required></div>
                <div>
                    <label class="erp-label">{{ __('Method') }}</label>
                    <select name="payment_method" class="erp-input w-full">
                        @foreach (App\Enums\CustomerPaymentMethod::cases() as $method)
                            <option value="{{ $method->value }}" @selected($payment->payment_method === $method)>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="erp-label">{{ __('Amount') }}</label><input type="number" name="amount" step="0.01" value="{{ $payment->amount }}" class="erp-input w-full" required></div>
                <div class="flex items-end"><label class="inline-flex gap-2 text-sm"><input type="checkbox" name="is_deposit" value="1" @checked($payment->is_deposit)>{{ __('Deposit') }}</label></div>
            </div>
        </x-admin.card>
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Allocations') }}</h3>
            @foreach ($openInvoices as $index => $inv)
                @php $existing = $payment->allocations->firstWhere('customer_invoice_id', $inv->id); @endphp
                <div class="flex gap-3 py-2 border-t border-erp-border text-sm">
                    <input type="hidden" name="allocations[{{ $index }}][customer_invoice_id]" value="{{ $inv->id }}">
                    <span class="flex-1 font-mono">{{ $inv->invoice_number }} ({{ number_format($inv->balance_due + ($existing?->amount ?? 0), 2) }})</span>
                    <input type="number" name="allocations[{{ $index }}][amount]" step="0.01" value="{{ $existing?->amount }}" class="erp-input w-32">
                </div>
            @endforeach
        </x-admin.card>
        <button type="submit" class="erp-btn-primary">{{ __('Save') }}</button>
    </form>
</x-admin-layout>
