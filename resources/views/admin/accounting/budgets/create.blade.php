<x-admin-layout :title="__('New Budget')" :breadcrumbs="[['label' => __('Budgets'), 'url' => route('admin.accounting.budgets.index')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('New Budget')" :description="__('Draft budget with one or more GL lines')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.accounting.budgets.store') }}" class="space-y-4" x-data="{ lineCount: 1 }">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2 max-w-3xl">
                <div class="sm:col-span-2">
                    <label class="erp-label">{{ __('Name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Fiscal year') }}</label>
                    <select name="fiscal_year_id" class="erp-input">
                        <option value="">{{ __('Optional') }}</option>
                        @foreach ($fiscalYears as $fy)
                            <option value="{{ $fy->id }}" @selected(old('fiscal_year_id') == $fy->id)>{{ $fy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div></div>
                <div>
                    <label class="erp-label">{{ __('From date') }}</label>
                    <input type="date" name="from_date" value="{{ old('from_date', now()->startOfYear()->toDateString()) }}" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('To date') }}</label>
                    <input type="date" name="to_date" value="{{ old('to_date', now()->endOfYear()->toDateString()) }}" class="erp-input" required>
                </div>
            </div>

            <div class="border-t border-erp-border pt-4">
                <h3 class="font-medium mb-2">{{ __('Budget lines') }}</h3>
                <template x-for="i in lineCount" :key="i">
                    <div class="grid gap-2 sm:grid-cols-3 mb-2">
                        <select :name="`lines[${i-1}][gl_account_id]`" class="erp-input" required>
                            <option value="">{{ __('GL account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" :name="`lines[${i-1}][period_month]`" class="erp-input" placeholder="{{ __('YYYY-MM optional') }}">
                        <input type="number" step="0.01" :name="`lines[${i-1}][amount]`" class="erp-input" placeholder="{{ __('Amount') }}" required>
                    </div>
                </template>
                <button type="button" class="erp-btn-secondary text-sm" @click="lineCount++">{{ __('Add line') }}</button>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Create budget') }}</button>
                <a href="{{ route('admin.accounting.budgets.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
