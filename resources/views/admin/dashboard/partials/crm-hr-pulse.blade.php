@php $crm = $dashboard['crm']; $hr = $dashboard['hr']; @endphp
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __('CRM Pulse') }}</h2></div>
    <div class="exec-metric-grid exec-metric-grid--2">
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Open Leads') }}</span><span class="exec-metric__value">{{ $crm['open_leads'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Customers Added') }}</span><span class="exec-metric__value">{{ $crm['customers_added'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Quotes Sent') }}</span><span class="exec-metric__value">{{ $crm['quotes_sent'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Conversion') }}</span><span class="exec-metric__value">{{ $crm['conversion_rate'] }}</span></div>
        <div class="exec-metric col-span-2"><span class="exec-metric__label">{{ __('Lost Opportunities') }}</span><span class="exec-metric__value">{{ $crm['lost_opportunities'] }}</span></div>
    </div>
</section>
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __('HR Pulse') }}</h2></div>
    <div class="exec-metric-grid exec-metric-grid--2">
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Staff Present') }}</span><span class="exec-metric__value">{{ $hr['present'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('On Leave') }}</span><span class="exec-metric__value">{{ $hr['on_leave'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Contract Expiry') }}</span><span class="exec-metric__value">{{ $hr['contract_expiry'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Performance Alerts') }}</span><span class="exec-metric__value">{{ $hr['performance_alerts'] }}</span></div>
    </div>
</section>
