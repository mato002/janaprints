<x-admin-layout :title="__('Trial Balance')">
    <x-admin.page-header :title="__('Trial Balance')" :description="__('Derived from posted journal lines only')" />

    @include('admin.accounting.partials.period-range-toolbar', [
        'action' => route('admin.accounting.reports.trial-balance'),
        'resetUrl' => route('admin.accounting.reports.trial-balance'),
        'filters' => $filters,
        'periods' => $periods,
        'showZeroCheckbox' => true,
        'full' => $full,
    ])

    <x-admin.trial-balance-enterprise :report="$report" table-mode="extended" />
</x-admin-layout>
