@if (! empty($kpis))
    <section class="mb-6" aria-label="{{ __('Commercial KPIs') }}">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8">
            @foreach ($kpis as $kpi)
                @if (! empty($kpi['href']))
                    <a href="{{ $kpi['href'] }}" data-turbo-frame="erp-main" class="block transition hover:opacity-90">
                        <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" :icon="$kpi['icon']" />
                    </a>
                @else
                    <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" :icon="$kpi['icon']" />
                @endif
            @endforeach
        </div>
    </section>
@endif
