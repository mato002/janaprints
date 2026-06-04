<section class="exec-panel exec-panel--attention" aria-label="{{ __('Attention center') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Attention Center') }}</h2>
        <span class="text-[10px] font-semibold uppercase tracking-wide text-red-600">{{ __('Action required') }}</span>
    </div>
    <div class="exec-attention-grid">
        @foreach ($dashboard['attention'] as $item)
            @php
                $count = $item['display'] ?? (string) ($item['count'] ?? 0);
                $href = ! empty($item['route']) && Route::has($item['route']) ? route($item['route']) : null;
                $badgeClass = match ($item['severity']) {
                    'danger' => 'exec-badge--danger',
                    'warning' => 'exec-badge--warning',
                    default => 'exec-badge--muted',
                };
            @endphp
            @if ($href)
                <a href="{{ $href }}" data-turbo-frame="erp-main" class="exec-attention-item exec-attention-item--link">
            @else
                <div class="exec-attention-item">
            @endif
                <span class="exec-attention-item__label">{{ $item['label'] }}</span>
                <span class="exec-badge {{ $badgeClass }}">{{ $count }}</span>
                @if (! empty($item['hint']))
                    <span class="exec-attention-item__hint">{{ $item['hint'] }}</span>
                @endif
            @if ($href)
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</section>
