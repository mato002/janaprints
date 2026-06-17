@php
    $periods = $payslips
        ->map(fn ($p) => $p->payrollRun?->period_start?->format('Y-m'))
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();
@endphp

<section class="space-y-4">
    @if ($periods->isNotEmpty())
        <form method="GET" action="{{ route('ess.dashboard') }}" class="ess-card flex flex-col gap-3 sm:flex-row sm:items-end">
            <input type="hidden" name="tab" value="payslips">
            <div class="flex-1">
                <label class="ess-label" for="period">{{ __('Filter by period') }}</label>
                <select id="period" name="period" class="ess-input w-full">
                    <option value="">{{ __('All periods') }}</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period }}" @selected(request('period') === $period)>{{ $period }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="ess-btn ess-btn--primary w-full sm:w-auto">{{ __('Apply filter') }}</button>
        </form>
    @endif

    @forelse ($payslips as $payslip)
        <article class="ess-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold">{{ $payslip->reference ?? __('Payslip') }}</p>
                    <p class="text-sm text-erp-muted">
                        {{ $payslip->payrollRun?->period_start?->format('d M Y') }}
                        —
                        {{ $payslip->payrollRun?->period_end?->format('d M Y') }}
                    </p>
                    <p class="mt-1 text-sm">{{ __('Net pay') }}: <strong>KES {{ number_format((float) $payslip->net_pay, 2) }}</strong></p>
                </div>
                <a href="{{ route('ess.payslips.download', $payslip) }}" class="ess-btn ess-btn--primary w-full sm:w-auto">{{ __('Download PDF') }}</a>
            </div>
        </article>
    @empty
        <div class="ess-card text-sm text-erp-muted">{{ __('No released payslips available.') }}</div>
    @endforelse
</section>
