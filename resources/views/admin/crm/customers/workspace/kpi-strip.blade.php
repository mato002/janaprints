<section class="c360-kpi-strip" aria-label="{{ __('Customer KPIs') }}">
    @foreach ($kpis as $kpi)
        <article class="c360-kpi-card">
            <div class="c360-kpi-card__head">
                @if (! empty($kpi['icon']))
                    <x-admin.icon :name="$kpi['icon']" class="h-4 w-4 text-erp-accent" />
                @endif
                <h3 class="c360-kpi-card__title">{{ $kpi['label'] }}</h3>
            </div>
            @if (! empty($kpi['metrics']))
                <dl class="c360-kpi-card__metrics">
                    @foreach ($kpi['metrics'] as $metric)
                        <div class="c360-kpi-card__metric">
                            <dt>{{ $metric['label'] }}</dt>
                            <dd class="tabular-nums">{{ $metric['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
            @if (! empty($kpi['warning']))
                <p class="mt-2 text-xs font-medium text-amber-700">{{ $kpi['warning'] }}</p>
            @endif
            @if (! empty($kpi['placeholder']))
                <p class="c360-kpi-card__placeholder {{ ! empty($kpi['metrics']) ? 'mt-2 border-t border-erp-border pt-2' : '' }}">{{ $kpi['placeholder'] }}</p>
            @endif
        </article>
    @endforeach
</section>
