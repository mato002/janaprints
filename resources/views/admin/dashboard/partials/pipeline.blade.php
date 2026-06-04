<section class="exec-panel" aria-label="{{ __('Production pipeline') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Production Pipeline') }}</h2>
    </div>
    <div class="exec-pipeline exec-pipeline--flow">
        @foreach ($dashboard['pipeline'] as $index => $stage)
            @if ($index > 0)
                <span class="exec-pipeline__arrow" aria-hidden="true"><span class="exec-pipeline__arrow-v">↓</span><span class="exec-pipeline__arrow-h">→</span></span>
            @endif
            @php
                $href = ! empty($stage['route']) && Route::has($stage['route']) ? route($stage['route']) : null;
            @endphp
            @if ($href)
                <a href="{{ $href }}" data-turbo-frame="erp-main" class="exec-pipeline__stage exec-pipeline__stage--link">
            @else
                <div class="exec-pipeline__stage">
            @endif
                <span class="exec-pipeline__label">{{ $stage['label'] }}</span>
                <span class="exec-pipeline__count">({{ $stage['count'] }})</span>
                <span class="exec-pipeline__bar" style="width: {{ max($stage['percent'], $stage['count'] > 0 ? 12 : 4) }}%"></span>
            @if ($href)
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</section>
