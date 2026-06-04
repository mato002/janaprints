<section class="exec-kpi-strip" aria-label="{{ __('Executive KPIs') }}">
    @foreach ($dashboard['kpi_strip'] as $kpi)
        @php
            $href = ! empty($kpi['route']) && Route::has($kpi['route']) ? route($kpi['route']) : null;
        @endphp
        @if ($href)
            <a href="{{ $href }}" data-turbo-frame="erp-main" data-turbo-action="advance" class="exec-kpi-cell exec-kpi-cell--link">
        @else
            <div class="exec-kpi-cell">
        @endif
            <span class="exec-kpi-cell__label">{{ $kpi['label'] }}</span>
            <span class="exec-kpi-cell__value">{{ $kpi['value'] }}</span>
            @if (! empty($kpi['hint']))
                <span class="exec-kpi-cell__hint">{{ $kpi['hint'] }}</span>
            @endif
        @if ($href)
            </a>
        @else
            </div>
        @endif
    @endforeach
</section>
