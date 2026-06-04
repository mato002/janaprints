@php $prod = $dashboard['production']; @endphp
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __('Production Performance') }}</h2></div>
    <div class="exec-metric-grid exec-metric-grid--3">
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Completed MTD') }}</span><span class="exec-metric__value">{{ $prod['completed_mtd'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Avg turnaround') }}</span><span class="exec-metric__value">{{ $prod['avg_turnaround'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Delayed') }}</span><span class="exec-metric__value text-red-600">{{ $prod['delayed'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('In progress') }}</span><span class="exec-metric__value">{{ $prod['in_progress'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Machine utilization') }}</span><span class="exec-metric__value">{{ $prod['machine_utilization'] }}%</span></div>
    </div>
</section>
