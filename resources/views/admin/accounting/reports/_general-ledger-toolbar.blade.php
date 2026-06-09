    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.accounting.reports.general-ledger')" :reset-url="route('admin.accounting.reports.general-ledger')">
            <input type="hidden" name="run" value="1">
            <select name="account_id" class="erp-toolbar-select min-w-[12rem]" aria-label="{{ __('Account') }}">
                <option value="">{{ __('Summary — all accounts') }}</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected(($filters['account_id'] ?? null) == $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                @endforeach
            </select>
            <select name="period_id" class="erp-toolbar-select" aria-label="{{ __('Period') }}">
                <option value="">{{ __('Custom') }}</option>
                @foreach ($periods as $period)
                    <option value="{{ $period->id }}" @selected(($filters['period_id'] ?? null) == $period->id)>{{ $period->code }}</option>
                @endforeach
            </select>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        </x-admin.index-toolbar>
    </x-admin.card>
