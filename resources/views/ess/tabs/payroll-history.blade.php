<section class="space-y-3">
    @forelse ($payrollHistory as $row)
        <article class="ess-card">
            <div class="flex flex-col gap-2">
                <p class="font-semibold">
                    {{ $row['period_start']?->format('d M Y') ?? '—' }}
                    —
                    {{ $row['period_end']?->format('d M Y') ?? '—' }}
                </p>
                <dl class="ess-dl ess-dl--compact">
                    <div><dt>{{ __('Gross pay') }}</dt><dd>KES {{ number_format($row['gross_pay'], 2) }}</dd></div>
                    <div><dt>{{ __('Deductions') }}</dt><dd>KES {{ number_format($row['total_deductions'], 2) }}</dd></div>
                    <div><dt>{{ __('Net pay') }}</dt><dd>KES {{ number_format($row['net_pay'], 2) }}</dd></div>
                    <div><dt>{{ __('Payment status') }}</dt><dd>{{ $row['payment_status'] }}</dd></div>
                    <div><dt>{{ __('Payment date') }}</dt><dd>{{ $row['pay_date']?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
            </div>
        </article>
    @empty
        <div class="ess-card text-sm text-erp-muted">{{ __('No payroll history available.') }}</div>
    @endforelse
</section>
