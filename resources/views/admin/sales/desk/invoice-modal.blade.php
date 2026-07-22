<x-admin.modal-form :title="__('Create invoice')" maxWidth="2xl">
    <form method="POST" action="{{ route('admin.invoices.store-from-sales-order', $salesOrder) }}" class="space-y-4" data-erp-desk-form x-data="{
        billingType: '{{ old('invoice_type', 'standard') }}',
        eligibility: @js($billingEligibilityByType),
        selectedEligibility() { return this.eligibility[this.billingType] ?? { eligible: true, blockers: [] }; }
    }">
        @csrf
        <input type="hidden" name="from" value="sales-desk">

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <p class="font-medium text-slate-900">{{ $salesOrder->order_number }}</p>
            <p class="text-xs text-slate-600">{{ $salesOrder->customer?->company_name }} · {{ __('Remaining billable') }}: {{ number_format($salesOrder->remainingInvoiceTotal(), 2) }}</p>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" x-show="! selectedEligibility().eligible" x-cloak>
            <template x-for="blocker in selectedEligibility().blockers" :key="blocker">
                <p x-text="blocker"></p>
            </template>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label">{{ __('Billing type') }}</label>
                <select name="invoice_type" class="erp-input w-full" x-model="billingType" required>
                    <option value="standard">{{ __('Full invoice') }}</option>
                    <option value="deposit">{{ __('Deposit') }}</option>
                    <option value="progress">{{ __('Progress billing') }}</option>
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Invoice date') }}</label>
                <input type="date" name="invoice_date" value="{{ old('invoice_date', now()->toDateString()) }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Due date') }}</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" class="erp-input w-full">
            </div>
            <div x-show="billingType === 'deposit'" x-cloak>
                <label class="erp-label">{{ __('Deposit amount') }}</label>
                <input type="number" name="deposit_amount" step="0.01" min="0.01" class="erp-input w-full" value="{{ old('deposit_amount') }}">
            </div>
        </div>

        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Create invoice') }}</button>
        </x-admin.form-modal-actions>
    </form>
</x-admin.modal-form>
