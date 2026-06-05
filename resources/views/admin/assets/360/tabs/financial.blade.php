@php $p = $tabData['profile']; @endphp
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <x-admin.kpi-widget :label="__('Acquisition Cost')" :value="number_format($p['acquisition_cost'], 2)" icon="currency-dollar" />
    <x-admin.kpi-widget :label="__('Net Book Value')" :value="number_format($p['net_book_value'], 2)" icon="chart-pie" />
    <x-admin.kpi-widget :label="__('Monthly Depreciation')" :value="number_format($p['monthly_depreciation'], 2)" icon="calendar" />
    <x-admin.kpi-widget :label="__('Annual Depreciation')" :value="number_format($p['annual_depreciation'], 2)" icon="trending-down" />
</div>
<x-admin.card class="mt-4">
    <h3 class="mb-3 text-sm font-semibold">{{ __('Finance Timeline') }}</h3>
    @include('admin.assets.360.partials.timeline', ['entries' => $tabData['timeline']])
</x-admin.card>
