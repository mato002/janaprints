<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <x-admin.kpi-widget :label="__('Downtime Hours')" :value="$tabData['downtime_hours']" icon="clock" />
    <x-admin.kpi-widget :label="__('Open Work Orders')" :value="$tabData['open_count']" icon="clipboard-list" />
    <x-admin.kpi-widget :label="__('Preventive')" :value="$tabData['preventive_count']" icon="shield-check" />
    <x-admin.kpi-widget :label="__('Corrective')" :value="$tabData['corrective_count']" icon="wrench" />
</div>
<x-admin.card class="mt-4">
    <h3 class="mb-3 text-sm font-semibold">{{ __('Maintenance Timeline') }}</h3>
    @include('admin.assets.360.partials.timeline', ['entries' => $tabData['timeline']])
</x-admin.card>
