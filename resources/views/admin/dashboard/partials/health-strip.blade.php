@php
    $kpis = collect($dashboard['kpi_strip'])->keyBy('key');
    $sales = $dashboard['sales'];
    $crm = $dashboard['crm'];
    $ops = $dashboard['today_ops'];
    $finance = $dashboard['finance'];
    $inventory = $dashboard['inventory'];
    $complaints = collect($dashboard['attention'])->firstWhere('key', 'complaints');

    $chips = [
        [
            'label' => __('Quotes'),
            'value' => $kpis->get('open_quotes')['value'] ?? '0',
            'route' => $kpis->get('open_quotes')['route'] ?? null,
        ],
        [
            'label' => __('Orders'),
            'value' => (string) ($sales['orders_mtd'] ?? 0),
            'route' => 'admin.sales-orders.index',
        ],
        [
            'label' => __('Customers'),
            'value' => '+'.($crm['customers_added'] ?? 0),
            'route' => 'admin.crm.customers.index',
        ],
        [
            'label' => __('Deliveries'),
            'value' => (string) ($ops['deliveries_today'] ?? 0),
            'route' => 'admin.sales-orders.index',
        ],
        [
            'label' => __('Payables'),
            'value' => $finance['payables'] ?? '—',
            'route' => null,
        ],
        [
            'label' => __('Inventory'),
            'value' => (string) ($inventory['reorder_alerts'] ?? 0).' '.__('alerts'),
            'route' => 'admin.inventory.dashboard',
        ],
        [
            'label' => __('Complaints'),
            'value' => (string) ($complaints['count'] ?? 0),
            'route' => $complaints['route'] ?? null,
        ],
        [
            'label' => __('Machine Utilization'),
            'value' => ($ops['machine_utilization'] ?? 0).'%',
            'route' => 'admin.production.job-cards.index',
        ],
    ];
@endphp

<section class="exec-health-strip" aria-label="{{ __('Business health') }}">
    @foreach ($chips as $chip)
        @php
            $href = ! empty($chip['route']) && Route::has($chip['route']) ? route($chip['route']) : null;
        @endphp
        <x-admin.exec-health-chip
            :label="$chip['label']"
            :value="$chip['value']"
            :href="$href"
        />
    @endforeach
</section>
