<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
    @foreach ([
        ['label' => __('Attendance (month)'), 'value' => $attendance['summary']['present'] ?? 0],
        ['label' => __('Late'), 'value' => $attendance['summary']['late'] ?? 0],
        ['label' => __('Leave pending'), 'value' => $leave['pending']->count()],
        ['label' => __('Gross pay'), 'value' => $overview['gross_salary'] ? number_format($overview['gross_salary'], 0) : '—'],
        ['label' => __('Documents'), 'value' => $documents['all']->total()],
        ['label' => __('Assets issued'), 'value' => $assets['issued']->count()],
    ] as $kpi)
        <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" />
    @endforeach
</div>
