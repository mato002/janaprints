<x-admin-layout :title="__('Trial Balance')">
    <x-admin.page-header :title="__('Trial Balance')" :description="__('Derived from posted journal lines only')" />

    <x-admin.card class="mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-[11px] text-slate-500">{{ __('Period') }}</label>
                <select name="period_id" class="erp-input mt-1">
                    <option value="">{{ __('Custom range') }}</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected(($filters['period_id'] ?? null) == $period->id)>{{ $period->code }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="text-[11px] text-slate-500">{{ __('From') }}</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-input mt-1"></div>
            <div><label class="text-[11px] text-slate-500">{{ __('To') }}</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-input mt-1"></div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="include_zero" value="0"><input type="checkbox" name="include_zero" value="1" @checked($full)>{{ __('Zero balances') }}
            </label>
            <button class="erp-btn-primary">{{ __('Run report') }}</button>
        </form>
    </x-admin.card>

    <x-admin.trial-balance-enterprise :report="$report" table-mode="extended" />
</x-admin-layout>
