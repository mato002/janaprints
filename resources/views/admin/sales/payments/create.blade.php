<x-admin-layout :title="__('Record payment')">
    <x-admin.page-header :title="__('Record customer payment')" />

    <form method="POST" action="{{ route('admin.payments.store') }}" class="max-w-3xl space-y-6">
        @csrf
        <x-admin.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="erp-label">{{ __('Customer') }}</label>
                    <select name="customer_id" class="erp-input w-full" required onchange="if(this.value) window.location='{{ route('admin.payments.create') }}?customer_id='+this.value">
                        <option value="">{{ __('Select customer') }}</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $customer?->id) == $c->id)>{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Payment date') }}</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Method') }}</label>
                    <select name="payment_method" class="erp-input w-full" required>
                        @foreach (App\Enums\CustomerPaymentMethod::cases() as $method)
                            <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Amount') }}</label>
                    <input type="number" name="amount" step="0.01" min="0.01" class="erp-input w-full" value="{{ old('amount') }}" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_deposit" value="1" @checked(old('is_deposit'))>
                        {{ __('Customer deposit (unallocated portion posts to deposits liability)') }}
                    </label>
                </div>
                <div>
                    <label class="erp-label">{{ __('Bank reference') }}</label>
                    <input type="text" name="bank_reference" class="erp-input w-full" value="{{ old('bank_reference') }}">
                </div>
                <div>
                    <label class="erp-label">{{ __('M-Pesa reference') }}</label>
                    <input type="text" name="mpesa_reference" class="erp-input w-full" value="{{ old('mpesa_reference') }}">
                </div>
            </div>
        </x-admin.card>

        @if ($customer && count($openInvoices) > 0)
            <x-admin.card>
                <h3 class="font-medium mb-3">{{ __('Allocate to invoices') }}</h3>
                <p class="text-sm text-slate-500 mb-3">{{ __('One payment can settle many invoices. Multiple payments can settle one invoice.') }}</p>
                @foreach ($openInvoices as $index => $inv)
                    <div class="flex flex-wrap items-center gap-3 py-2 border-t border-erp-border text-sm">
                        <input type="hidden" name="allocations[{{ $index }}][customer_invoice_id]" value="{{ $inv->id }}">
                        <span class="flex-1 font-mono">{{ $inv->invoice_number }}</span>
                        <span class="text-slate-500">{{ __('Due') }} {{ number_format($inv->balance_due, 2) }}</span>
                        <input type="number" name="allocations[{{ $index }}][amount]" step="0.01" min="0" max="{{ $inv->balance_due }}" class="erp-input w-32" placeholder="0.00">
                    </div>
                @endforeach
            </x-admin.card>
        @endif

        <button type="submit" class="erp-btn-primary">{{ __('Save draft payment') }}</button>
    </form>
</x-admin-layout>
