@php
    $groups = [
        'critical' => ['label' => __('Critical'), 'class' => 'exec-attention-group--critical', 'severities' => ['danger']],
        'warning' => ['label' => __('Warning'), 'class' => 'exec-attention-group--warning', 'severities' => ['warning']],
        'normal' => ['label' => __('Normal'), 'class' => 'exec-attention-group--normal', 'severities' => ['muted']],
    ];

    $itemsBySeverity = collect($dashboard['attention'])->groupBy('severity');
@endphp

<section class="exec-panel exec-panel--attention" aria-label="{{ __('Attention center') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Attention Center') }}</h2>
        <span class="exec-attention-ribbon">{{ __('Action required') }}</span>
    </div>

    <div class="exec-attention-groups">
        @foreach ($groups as $groupKey => $group)
            @php
                $items = collect($dashboard['attention'])
                    ->filter(fn ($item) => in_array($item['severity'], $group['severities'], true));
            @endphp
            @if ($items->isNotEmpty())
                <div class="exec-attention-group {{ $group['class'] }}">
                    <h3 class="exec-attention-group__title">{{ $group['label'] }}</h3>
                    <ul class="exec-attention-list" role="list">
                        @foreach ($items as $item)
                            @php
                                $count = $item['display'] ?? (string) ($item['count'] ?? 0);
                                $numericCount = is_numeric($item['count'] ?? null) ? (int) $item['count'] : null;
                                $href = ! empty($item['route']) && Route::has($item['route']) ? route($item['route']) : null;
                                $isClear = $numericCount === 0 && ($item['display'] ?? null) !== '—';
                                $badgeClass = match (true) {
                                    $isClear && $groupKey === 'critical' => 'exec-alert-badge--clear',
                                    $item['severity'] === 'danger' => 'exec-alert-badge--critical',
                                    $item['severity'] === 'warning' => 'exec-alert-badge--warning',
                                    default => 'exec-alert-badge--normal',
                                };
                            @endphp
                            <li>
                                @if ($href)
                                    <a href="{{ $href }}" data-turbo-frame="erp-main" class="exec-alert-row exec-alert-row--link">
                                @else
                                    <div class="exec-alert-row">
                                @endif
                                    <span class="exec-alert-row__label">{{ $item['label'] }}</span>
                                    <span class="exec-alert-badge {{ $badgeClass }}">
                                        @if ($isClear && $groupKey === 'critical')
                                            {{ __('Clear') }}
                                        @else
                                            {{ $count }}
                                        @endif
                                    </span>
                                    @if (! empty($item['hint']))
                                        <span class="exec-alert-row__hint">{{ $item['hint'] }}</span>
                                    @endif
                                @if ($href)
                                    </a>
                                @else
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </div>
</section>
