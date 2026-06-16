<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
    @foreach ([
        ['label' => __('Employees'), 'value' => number_format($overview['employee_count'])],
        ['label' => __('Gross pay'), 'value' => number_format($overview['gross_total'], 2)],
        ['label' => __('Deductions'), 'value' => number_format($overview['deductions_total'], 2)],
        ['label' => __('Net pay'), 'value' => number_format($overview['net_total'], 2)],
        ['label' => __('Pay date'), 'value' => $overview['pay_date']?->format('M j, Y') ?? '—'],
    ] as $kpi)
        <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" />
    @endforeach
</div>
