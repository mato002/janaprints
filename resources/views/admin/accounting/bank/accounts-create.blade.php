<x-admin-layout :title="__('Add Bank Account')" :breadcrumbs="[['label' => __('Bank Accounts'), 'url' => route('admin.accounting.bank.accounts.index')], ['label' => __('Add')]]">
    <x-admin.page-header :title="__('Add Bank Account')" :description="__('Map a postable asset GL account for bank reconciliation')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.accounting.bank.accounts.store') }}" class="space-y-4 max-w-xl">
            @csrf
            <div>
                <label class="erp-label">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" class="erp-input" required>
            </div>
            <div>
                <label class="erp-label">{{ __('GL account') }}</label>
                <select name="gl_account_id" class="erp-input" required>
                    <option value="">{{ __('Select…') }}</option>
                    @foreach ($glAccounts as $gl)
                        <option value="{{ $gl->id }}" @selected(old('gl_account_id') == $gl->id)>{{ $gl->code }} — {{ $gl->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Account number') }}</label>
                <input type="text" name="account_number" value="{{ old('account_number') }}" class="erp-input">
            </div>
            <div>
                <label class="erp-label">{{ __('Currency') }}</label>
                <input type="text" name="currency_code" value="{{ old('currency_code', 'KES') }}" maxlength="3" class="erp-input" required>
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                {{ __('Active') }}
            </label>
            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.accounting.bank.accounts.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
