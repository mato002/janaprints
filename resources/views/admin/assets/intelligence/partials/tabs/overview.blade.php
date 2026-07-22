<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
    <x-admin.kpi-widget :label="__('Total Asset Value')" :value="number_format($stats['total_asset_value'], 2)" icon="chip" />
    <x-admin.kpi-widget :label="__('Net Book Value')" :value="number_format($stats['net_book_value'], 2)" icon="chart-pie" />
    <x-admin.kpi-widget :label="__('Depreciation This Month')" :value="number_format($stats['depreciation_this_month'], 2)" icon="trending-down" />
    <x-admin.kpi-widget :label="__('Near End of Life')" :value="$stats['assets_near_end_of_life']" icon="calendar" />
    <x-admin.kpi-widget :label="__('Under Maintenance')" :value="$stats['assets_under_maintenance']" icon="wrench" />
    <x-admin.kpi-widget :label="__('Critical Assets')" :value="$stats['critical_assets']" icon="exclamation" />
    <x-admin.kpi-widget :label="__('Warranty Expiring')" :value="$stats['warranty_expiring']" icon="shield-check" />
    <x-admin.kpi-widget :label="__('Replacement Candidates')" :value="$stats['replacement_candidates']" icon="switch-horizontal" />
</div>

<p class="mt-4 text-xs text-slate-500">
    {{ __('Open any asset from the register and choose View 360 for complete lifecycle intelligence.') }}
</p>
