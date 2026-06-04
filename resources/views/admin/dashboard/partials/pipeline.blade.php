@php
    $totalPipeline = max(1, collect($dashboard['pipeline'])->sum('count'));
@endphp

<section class="exec-panel exec-panel--pipeline" aria-label="{{ __('Production command center') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Production Command Center') }}</h2>
        <span class="exec-panel__meta">{{ __('Live flow') }}</span>
    </div>
    <div class="exec-pipeline-board">
        @foreach ($dashboard['pipeline'] as $index => $stage)
            @if ($index > 0)
                <span class="exec-pipeline-board__connector" aria-hidden="true">
                    <span class="exec-pipeline-board__arrow-h">→</span>
                    <span class="exec-pipeline-board__arrow-v">↓</span>
                </span>
            @endif
            @php
                $href = ! empty($stage['route']) && Route::has($stage['route']) ? route($stage['route']) : null;
                $flowPct = (int) round(($stage['count'] / $totalPipeline) * 100);
                $barPct = max($stage['percent'], $stage['count'] > 0 ? 12 : 6);
                $stageClass = 'exec-pipeline-board__stage--'.$stage['key'];
            @endphp
            @if ($href)
                <a href="{{ $href }}" data-turbo-frame="erp-main" class="exec-pipeline-board__stage exec-pipeline-board__stage--link {{ $stageClass }}">
            @else
                <div class="exec-pipeline-board__stage {{ $stageClass }}">
            @endif
                <span class="exec-pipeline-board__label">{{ $stage['label'] }}</span>
                <span class="exec-pipeline-board__count">{{ number_format($stage['count']) }}</span>
                <div class="exec-pipeline-board__track">
                    <div class="exec-pipeline-board__bar" style="width: {{ $barPct }}%"></div>
                </div>
                <span class="exec-pipeline-board__flow">{{ $flowPct }}% {{ __('of pipeline') }}</span>
            @if ($href)
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</section>
