<x-admin-layout :title="__('New Bank Statement')" :breadcrumbs="[['label' => __('Bank Reconciliation'), 'url' => route('admin.accounting.bank.reconciliation.index')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('New Bank Statement')" :description="__('Create a statement and optionally import lines')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.accounting.bank.reconciliation.store') }}" class="space-y-4" x-data="{ lineCount: 1 }">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2 max-w-3xl">
                <div class="sm:col-span-2">
                    <label class="erp-label">{{ __('Bank account') }}</label>
                    <select name="bank_account_id" class="erp-input" required>
                        <option value="">{{ __('Select…') }}</option>
                        @foreach ($bankAccounts as $account)
                            <option value="{{ $account->id }}" @selected(old('bank_account_id') == $account->id)>
                                {{ $account->name }} ({{ $account->glAccount?->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Statement date') }}</label>
                    <input type="date" name="statement_date" value="{{ old('statement_date', now()->toDateString()) }}" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Opening balance') }}</label>
                    <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Closing balance') }}</label>
                    <input type="number" step="0.01" name="closing_balance" value="{{ old('closing_balance', 0) }}" class="erp-input" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label">{{ __('Notes') }}</label>
                    <textarea name="notes" class="erp-input" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="border-t border-erp-border pt-4">
                <h3 class="font-medium mb-2">{{ __('Statement lines (optional)') }}</h3>
                <p class="text-xs text-slate-500 mb-3">{{ __('Use signed amounts: positive for deposits (DR bank), negative for withdrawals (CR bank).') }}</p>
                <template x-for="i in lineCount" :key="i">
                    <div class="grid gap-2 sm:grid-cols-4 mb-2">
                        <input type="date" :name="`lines[${i-1}][line_date]`" class="erp-input" :value="{{ json_encode(now()->toDateString()) }}">
                        <input type="text" :name="`lines[${i-1}][description]`" class="erp-input" placeholder="{{ __('Description') }}">
                        <input type="text" :name="`lines[${i-1}][reference]`" class="erp-input" placeholder="{{ __('Reference') }}">
                        <input type="number" step="0.01" :name="`lines[${i-1}][amount]`" class="erp-input" placeholder="{{ __('Amount') }}">
                    </div>
                </template>
                <button type="button" class="erp-btn-secondary text-sm" @click="lineCount++">{{ __('Add line') }}</button>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Create statement') }}</button>
                <a href="{{ route('admin.accounting.bank.reconciliation.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
