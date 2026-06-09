<x-admin-layout :title="__('Trial Balance')">
    <x-admin.page-header :title="__('Trial Balance')" :description="__('Derived from posted journal lines only')" />

    @include('admin.accounting.partials.period-range-toolbar', [
        'action' => route('admin.accounting.trial-balance.index'),
        'resetUrl' => route('admin.accounting.trial-balance.index'),
        'filters' => $filters,
        'periods' => $periods,
        'showZeroCheckbox' => true,
        'full' => $full,
        'customPeriodLabel' => __('All posted'),
    ])

    <x-admin.trial-balance-enterprise :report="$report" table-mode="standard" />
</x-admin-layout>
