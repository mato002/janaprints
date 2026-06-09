<x-admin-layout :title="__('Apply deposit')">
    <x-admin.page-header :title="__('Apply deposit to invoice')" :description="$invoice->invoice_number.' · '.$invoice->customer?->company_name">
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="erp-btn-secondary">{{ __('Back to invoice') }}</a>
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
        <x-admin.kpi-widget :label="__('Balance due')" :value="number_format($invoice->balance_due, 2)" icon="scale" />
        <x-admin.kpi-widget :label="__('Total')" :value="number_format($invoice->total_amount, 2)" icon="currency-dollar" />
    </div>

    @include('admin.sales.deposits.partials.wallet', ['customerId' => $invoice->customer_id])

    @if ($deposits === [])
        <x-admin.card>
            <p class="text-sm text-slate-500">{{ __('No posted deposits with remaining credit for this customer.') }}</p>
        </x-admin.card>
    @else
        <x-admin.card>
            <form method="POST" action="{{ route('admin.invoices.apply-deposit.store', $invoice) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Deposit payment') }}</label>
                    <select name="customer_payment_id" class="erp-input w-full" required>
                        @foreach ($deposits as $deposit)
                            @php($isCrossBranch = $deposit->branch_id !== $invoice->branch_id)
                            <option value="{{ $deposit->id }}" @selected(old('customer_payment_id') == $deposit->id)>
                                {{ $deposit->payment_number }}
                                · {{ number_format($deposit->credit_remaining, 2) }} {{ __('remaining') }}
                                @if ($deposit->branch)
                                    · {{ $deposit->branch->name }}
                                @endif
                                @if ($isCrossBranch)
                                    · {{ __('Cross-branch') }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if ($canCrossBranch ?? false)
                        <p class="mt-1 text-xs text-slate-500">{{ __('Cross-branch deposits require an override reason below.') }}</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Amount to apply') }}</label>
                    <input type="number" name="amount" step="0.01" min="0.01" max="{{ $invoice->balance_due }}" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Application date') }}</label>
                    <input type="date" name="application_date" value="{{ now()->toDateString() }}" class="erp-input w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2" class="erp-input w-full">{{ old('notes') }}</textarea>
                </div>
                @if ($canCrossBranch ?? false)
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('Cross-branch override reason') }}</label>
                        <textarea name="override_reason" rows="2" class="erp-input w-full" placeholder="{{ __('Required when deposit branch differs from invoice branch') }}">{{ old('override_reason') }}</textarea>
                        @error('override_reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                <button type="submit" class="erp-btn-primary">{{ __('Apply deposit and post') }}</button>
            </form>
        </x-admin.card>
    @endif
</x-admin-layout>
