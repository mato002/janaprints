<x-admin.card class="mb-4">
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Net Pay Trend') }}</h3>
    @if ($payroll['net_trend']->isNotEmpty())
        <div class="flex flex-wrap gap-3">
            @foreach ($payroll['net_trend'] as $point)
                <div class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <div class="text-xs text-slate-500">{{ $point['period'] }}</div>
                    <div class="font-medium">{{ number_format($point['net'], 2) }}</div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500">{{ __('No payslip history yet.') }}</p>
    @endif
</x-admin.card>

<x-admin.card>
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Payslips') }}</h3>
    @include('admin.employees.tabs.payroll-history', ['payslips' => $payroll['payslips']])
</x-admin.card>
