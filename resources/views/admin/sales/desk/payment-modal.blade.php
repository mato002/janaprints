<x-admin.modal-form :title="__('Record payment')" maxWidth="2xl">
    <form method="POST" action="{{ route('admin.payments.store') }}" class="space-y-4" data-erp-desk-form>
        @csrf
        <input type="hidden" name="from" value="sales-desk">
        <input type="hidden" name="customer_id" value="{{ $customer?->id ?? old('customer_id') }}">
        @if ($salesOrder)
            <input type="hidden" name="sales_order_id" value="{{ $salesOrder->id }}">
        @endif

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <p class="font-medium text-slate-900">{{ $customer?->company_name ?? __('Customer') }}</p>
            @if ($salesOrder)
                <p class="text-xs text-slate-600">{{ __('Order') }} {{ $salesOrder->order_number }} · {{ number_format((float) $salesOrder->total_amount, 2) }}</p>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label">{{ __('Payment date') }}</label>
                <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Method') }}</label>
                <select name="payment_method" class="erp-input w-full" required>
                    @foreach (App\Enums\CustomerPaymentMethod::cases() as $method)
                        <option value="{{ $method->value }}" @selected(old('payment_method', 'cash') === $method->value)>{{ $method->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Amount') }}</label>
                <input type="number" name="amount" step="0.01" min="0.01" class="erp-input w-full" value="{{ old('amount', $defaultAmount ?? null) }}" required>
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_deposit" value="1" @checked(old('is_deposit'))>
                    {{ __('Customer deposit') }}
                </label>
            </div>
            <div>
                <label class="erp-label">{{ __('M-Pesa reference') }}</label>
                <input type="text" name="mpesa_reference" class="erp-input w-full" value="{{ old('mpesa_reference') }}">
            </div>
            <div>
                <label class="erp-label">{{ __('Bank reference') }}</label>
                <input type="text" name="bank_reference" class="erp-input w-full" value="{{ old('bank_reference') }}">
            </div>
        </div>

        @if ($customer && count($openInvoices ?? []) > 0)
            <div class="rounded-lg border border-erp-border p-3">
                <h3 class="mb-2 text-sm font-medium">{{ __('Allocate to invoices') }}</h3>
                @foreach ($openInvoices as $index => $inv)
                    <div class="flex flex-wrap items-center gap-3 py-2 border-t border-erp-border text-sm first:border-t-0">
                        <input type="hidden" name="allocations[{{ $index }}][customer_invoice_id]" value="{{ $inv->id }}">
                        <span class="flex-1 font-mono">{{ $inv->invoice_number }}</span>
                        <span class="text-slate-500">{{ number_format($inv->balance_due, 2) }}</span>
                        <input type="number" name="allocations[{{ $index }}][amount]" step="0.01" min="0" max="{{ $inv->balance_due }}" class="erp-input w-28" placeholder="0.00">
                    </div>
                @endforeach
            </div>
        @endif

        <x-admin.form-modal-actions>
            @can('payments.post')
                <button type="submit" name="post_now" value="1" class="erp-btn-primary">{{ __('Record payment') }}</button>
            @endcan
            <button type="submit" class="erp-btn-secondary">{{ __('Save draft') }}</button>
        </x-admin.form-modal-actions>
    </form>
</x-admin.modal-form>
