@php
    $healthMetrics = [
        ['label' => __('Open Threads'), 'value' => $stats['open'], 'tone' => $stats['open'] > 0 ? 'default' : 'muted'],
        ['label' => __('Waiting Customer'), 'value' => $stats['waiting_customer'], 'tone' => $stats['waiting_customer'] > 0 ? 'warning' : 'muted'],
        ['label' => __('Waiting Team'), 'value' => $stats['waiting_internal'], 'tone' => $stats['waiting_internal'] > 0 ? 'warning' : 'muted'],
        ['label' => __('SLA Breaches'), 'value' => $stats['overdue'], 'tone' => $stats['overdue'] > 0 ? 'danger' : 'success'],
        ['label' => __('Escalations'), 'value' => $stats['escalated'], 'tone' => $stats['escalated'] > 0 ? 'danger' : 'muted'],
    ];
@endphp

<section class="exec-panel exec-inbox-cc__section-panel" aria-label="{{ __('Communication health') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Communication Health') }}</h2>
        <span class="exec-panel__meta">{{ __(':active active · :unread unread', ['active' => $stats['active'], 'unread' => $stats['unread_total']]) }}</span>
    </div>
    <div class="exec-metric-grid exec-metric-grid--5">
        @foreach ($healthMetrics as $metric)
            @php
                $valueClass = match ($metric['tone']) {
                    'danger' => 'text-red-600',
                    'warning' => 'text-amber-600',
                    'success' => 'text-emerald-600',
                    default => 'text-erp-primary',
                };
            @endphp
            <div class="exec-metric exec-inbox-cc__metric-tile">
                <span class="exec-metric__label">{{ $metric['label'] }}</span>
                <span class="exec-metric__value {{ $valueClass }}">{{ $metric['value'] }}</span>
            </div>
        @endforeach
    </div>
</section>
