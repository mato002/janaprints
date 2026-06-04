@php $ops = $dashboard['today_ops']; @endphp
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __("Today's Operations") }}</h2></div>
    <div class="exec-metric-grid exec-metric-grid--5">
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Jobs scheduled today') }}</span><span class="exec-metric__value">{{ $ops['jobs_today'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Machine utilization') }}</span><span class="exec-metric__value">{{ $ops['machine_utilization'] }}%</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Deliveries today') }}</span><span class="exec-metric__value">{{ $ops['deliveries_today'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Collections expected') }}</span><span class="exec-metric__value">{{ $ops['collections_display'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Purchases awaiting approval') }}</span><span class="exec-metric__value">{{ $ops['purchases_pending'] }}</span></div>
    </div>
</section>
