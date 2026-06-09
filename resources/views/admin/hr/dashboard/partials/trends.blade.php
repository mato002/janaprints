@props(['trends'])

<section class="mt-6 grid gap-4 lg:grid-cols-2" aria-label="{{ __('Workforce trends') }}">
    @foreach ([
        'attendance' => __('Attendance Trend'),
        'payroll' => __('Payroll Trend'),
        'leave' => __('Leave Trend'),
        'headcount' => __('Headcount Trend'),
    ] as $key => $title)
        @php
            $points = $trends[$key] ?? [];
            $hasData = collect($points)->sum('value') > 0;
        @endphp
        <x-admin.card>
            <div class="mb-4 flex items-center justify-between gap-2">
                <h3 class="text-sm font-semibold text-erp-primary">{{ $title }}</h3>
                <span class="text-xs text-slate-500">{{ __('6 months') }}</span>
            </div>
            @if ($hasData)
                <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="{{ $title }}">
                    @foreach ($points as $point)
                        <div class="exec-bar-chart__col" title="{{ $point['label'] }}: {{ number_format($point['value'], $key === 'payroll' ? 0 : 1) }}">
                            <div class="exec-bar-chart__bar" style="height: {{ max($point['percent'], 4) }}%"></div>
                            <span class="exec-bar-chart__label">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">{{ __('No trend data for this period.') }}</p>
            @endif
        </x-admin.card>
    @endforeach
</section>
