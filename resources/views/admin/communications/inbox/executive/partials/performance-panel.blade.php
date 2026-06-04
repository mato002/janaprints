@php
    $firstResponse = $stats['avg_first_response_minutes'] !== null
        ? $stats['avg_first_response_minutes'].'m'
        : '—';
@endphp

<section class="exec-panel exec-inbox-cc__section-panel" aria-label="{{ __('Response performance') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Response Performance') }}</h2>
        <span class="exec-panel__meta">{{ __(':closed closed today', ['closed' => $stats['closed_today']]) }}</span>
    </div>
    <div class="exec-metric-grid exec-metric-grid--2 sm:grid-cols-4">
        <div class="exec-metric exec-inbox-cc__metric-tile">
            <span class="exec-metric__label">{{ __('Avg First Response') }}</span>
            <span class="exec-metric__value">{{ $firstResponse }}</span>
        </div>
        <div class="exec-metric exec-inbox-cc__metric-tile">
            <span class="exec-metric__label">{{ __('Avg Resolution Time') }}</span>
            <span class="exec-metric__value text-slate-400">—</span>
            <span class="exec-inbox-cc__metric-foot">{{ __('Not tracked on this view') }}</span>
        </div>
        <div class="exec-metric exec-inbox-cc__metric-tile">
            <span class="exec-metric__label">{{ __('Customer Satisfaction') }}</span>
            <span class="exec-metric__value text-slate-400">—</span>
            <span class="exec-inbox-cc__metric-foot">{{ __('Survey data pending') }}</span>
        </div>
        <div class="exec-metric exec-inbox-cc__metric-tile">
            <span class="exec-metric__label">{{ __('Volume Today') }}</span>
            <span class="exec-metric__value text-erp-accent">{{ $stats['volume_today'] }}</span>
            <span class="exec-inbox-cc__metric-foot">{{ __(':unanswered unanswered', ['unanswered' => $stats['unanswered']]) }}</span>
        </div>
    </div>
</section>
