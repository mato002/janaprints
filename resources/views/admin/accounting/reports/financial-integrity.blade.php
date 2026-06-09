<x-admin-layout :title="__('Financial Integrity')">
    <x-admin.page-header :title="__('Financial Integrity')" :description="__('Trial balance and balance sheet equation checks')" />

    @include('admin.accounting.partials.as-of-toolbar', [
        'action' => route('admin.accounting.reports.financial-integrity'),
        'resetUrl' => route('admin.accounting.reports.financial-integrity'),
        'filters' => $filters,
        'periods' => $periods,
        'periodLabel' => __('Period filter (optional)'),
        'nonePeriodLabel' => __('All posted through date'),
    ])

    @if ($report)
        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <x-admin.kpi-widget
                :label="__('Trial balance')"
                :value="$report['trial_balance_balanced'] ? __('Balanced') : __('Out of balance')"
                :icon="$report['trial_balance_balanced'] ? 'check-circle' : 'exclamation-circle'"
            />
            <x-admin.kpi-widget
                :label="__('Balance sheet equation')"
                :value="$report['balance_sheet_balanced'] ? __('A = L + E') : __('Variance').': '.number_format($report['variance'], 2)"
                :icon="$report['balance_sheet_balanced'] ? 'check-circle' : 'exclamation-circle'"
            />
        </div>

        <x-admin.card>
            <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('Trial balance debits') }}</dt><dd class="font-medium">{{ number_format($report['trial_balance_debit'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Trial balance credits') }}</dt><dd class="font-medium">{{ number_format($report['trial_balance_credit'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Total assets') }}</dt><dd class="font-medium">{{ number_format($report['total_assets'], 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Liabilities + equity') }}</dt><dd class="font-medium">{{ number_format($report['total_liabilities_and_equity'], 2) }}</dd></div>
            </dl>
        </x-admin.card>
    @endif
</x-admin-layout>
