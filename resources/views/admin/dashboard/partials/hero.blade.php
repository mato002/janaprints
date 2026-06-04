@php
    $kpis = collect($dashboard['kpi_strip'])->keyBy('key');
    $finance = $dashboard['finance'];
    $production = $dashboard['production'];

    $salesToday = $kpis->get('sales_today');
    $activeJobs = $kpis->get('active_jobs');
    $receivables = $kpis->get('receivables');
    $profitMtd = $finance['profit_mtd'] ?? '—';
    $revenueMtd = $finance['revenue_mtd'] ?? '—';

    $salesRoute = $salesToday && ! empty($salesToday['route']) && Route::has($salesToday['route'])
        ? route($salesToday['route']) : null;
    $jobsRoute = $activeJobs && ! empty($activeJobs['route']) && Route::has($activeJobs['route'])
        ? route($activeJobs['route']) : null;

    $salesRaw = $salesToday['value'] ?? '0';
    $salesEmpty = $salesRaw === 'KES 0' || $salesRaw === '0';
    $jobsCount = (int) ($activeJobs['value'] ?? 0);
    $delayedJobs = (int) ($production['delayed'] ?? 0);
@endphp

<section class="exec-hero" aria-label="{{ __('Executive summary') }}">
    <x-admin.exec-hero-metric
        :label="__('Today\'s Revenue')"
        :value="$salesToday['value'] ?? 'KES 0'"
        :href="$salesRoute"
        :empty="$salesEmpty"
        :subtext="$salesEmpty ? __('No revenue recorded today') : null"
    />
    <x-admin.exec-hero-metric
        :label="__('Jobs In Production')"
        :value="(string) $jobsCount"
        :href="$jobsRoute"
        :empty="$jobsCount === 0"
        :subtext="$jobsCount === 0 ? __('No jobs in production') : ($delayedJobs > 0 ? __(':count overdue', ['count' => $delayedJobs]) : null)"
    />
    <x-admin.exec-hero-metric
        :label="__('Outstanding Receivables')"
        :value="$receivables['value'] ?? '—'"
        :hint="($receivables['hint'] ?? null) ?: (($receivables['value'] ?? '—') === '—' ? __('Finance module') : null)"
        :empty="($receivables['value'] ?? '—') === '—'"
    />
    <x-admin.exec-hero-metric
        :label="__('Net Profit MTD')"
        :value="$profitMtd"
        :hint="$profitMtd === '—' ? __('Finance module') : null"
        :subtext="$profitMtd === '—' && $revenueMtd !== '—' ? __('Revenue MTD: :amount', ['amount' => $revenueMtd]) : null"
        :empty="$profitMtd === '—'"
    />
</section>
