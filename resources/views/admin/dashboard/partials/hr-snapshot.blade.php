@php
    $hr = $dashboard['hr_snapshot'] ?? null;
    $links = $hr['links'] ?? [];
@endphp

@if (! empty($hr['visible']))
    <section class="exec-panel exec-panel--hr">
        <div class="exec-panel__head exec-panel__head--split">
            <h2 class="exec-panel__title">{{ __('HR Snapshot') }}</h2>
            @if ($links !== [])
                <nav class="exec-finance-links" aria-label="{{ __('HR intelligence') }}">
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}" data-turbo-frame="erp-main" class="exec-finance-links__item">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
        <dl class="exec-dl exec-dl--grid">
            <div class="exec-dl__row"><dt>{{ __('Employees') }}</dt><dd>{{ $hr['employees'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Present Today') }}</dt><dd>{{ $hr['present_today'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Attendance %') }}</dt><dd>{{ $hr['attendance_percent'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Employees On Leave') }}</dt><dd>{{ $hr['on_leave'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Payroll Cost MTD') }}</dt><dd>{{ $hr['payroll_cost_mtd'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Overtime Cost') }}</dt><dd>{{ $hr['overtime_cost'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Contract Expiry') }}</dt><dd>{{ $hr['contract_expiry'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Training Due') }}</dt><dd>{{ $hr['training_due'] ?? '—' }}</dd></div>
            <div class="exec-dl__row"><dt>{{ __('Performance Alerts') }}</dt><dd>{{ $hr['performance_alerts'] ?? '—' }}</dd></div>
        </dl>
    </section>
@endif
