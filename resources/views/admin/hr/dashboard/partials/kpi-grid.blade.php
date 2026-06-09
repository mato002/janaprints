@props(['kpis'])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5">
    @foreach ($kpis as $kpi)
        @continue(! empty($kpi['hidden']))
        <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" :icon="$kpi['icon']" />
    @endforeach
</div>
