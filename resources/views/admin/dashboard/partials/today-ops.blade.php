@php
    $ops = $dashboard['today_ops'];
    $utilization = (int) ($ops['machine_utilization'] ?? 0);
    $utilVariant = $utilization >= 75 ? 'success' : ($utilization >= 40 ? 'default' : 'warning');
    $purchases = (int) ($ops['purchases_pending'] ?? 0);
    $purchasePct = min(100, $purchases * 20);
@endphp

<section class="exec-panel">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __("Today's Operations") }}</h2>
    </div>
    <div class="exec-ops-grid">
        <x-admin.exec-progress-widget
            :label="__('Machine utilization')"
            :value="$utilization.'%'"
            :percent="$utilization"
            :variant="$utilVariant"
        />
        <x-admin.exec-progress-widget
            :label="__('Deliveries today')"
            :value="(string) ($ops['deliveries_today'] ?? 0)"
            :percent="min(100, ((int) ($ops['deliveries_today'] ?? 0)) * 15)"
        />
        <x-admin.exec-progress-widget
            :label="__('Jobs scheduled today')"
            :value="(string) ($ops['jobs_today'] ?? 0)"
            :percent="min(100, ((int) ($ops['jobs_today'] ?? 0)) * 12)"
        />
        <x-admin.exec-progress-widget
            :label="__('Purchases awaiting approval')"
            :value="(string) $purchases"
            :percent="$purchasePct"
            :variant="$purchases > 0 ? 'warning' : 'default'"
        />
        <div class="exec-ops-grid__wide">
            <x-admin.exec-progress-widget
                :label="__('Collections expected')"
                :value="$ops['collections_display'] ?? '—'"
                :percent="($ops['collections_display'] ?? '—') === '—' ? null : 50"
            />
            @if (($ops['collections_display'] ?? '—') === '—')
                <p class="mt-1 text-[10px] text-slate-500">{{ __('Collections tracking connects with finance.') }}</p>
            @endif
        </div>
    </div>
</section>
