<x-admin.card>
    <h3 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Run summary') }}</h3>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            [__('Reference'), $overview['reference']],
            [__('Payroll group'), $overview['payroll_group_label'] ?? $overview['payroll_group'] ?? '—'],
            [__('Branch'), $overview['branch']],
            [__('Period start'), $overview['period_start']?->format('M j, Y')],
            [__('Period end'), $overview['period_end']?->format('M j, Y')],
            [__('Pay date'), $overview['pay_date']?->format('M j, Y')],
            [__('Status'), $overview['status']?->label()],
            [__('Approval status'), $overview['approval_status']],
            [__('Posting status'), $overview['posting_status']],
        ] as [$label, $value])
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                <dd class="mt-1 text-sm font-medium text-slate-900">{{ $value ?? '—' }}</dd>
            </div>
        @endforeach
    </dl>

    @if ($overview['notes'])
        <div class="mt-4 border-t border-slate-100 pt-4">
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Notes') }}</dt>
            <dd class="mt-1 text-sm text-slate-700">{{ $overview['notes'] }}</dd>
        </div>
    @endif
</x-admin.card>

@if ($run->payslips->isEmpty())
    <x-admin.card class="mt-4">
        <x-admin.empty-state
            :title="__('No payroll lines yet')"
            :description="__('Generate payroll to pull active employees, compensation, allowances, deductions, and attendance impact.')"
        />
        @can('process', $run)
            @if ($run->status->canGenerate())
                <form method="POST" action="{{ route('admin.hr.payroll.generate', $run) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Generate payroll') }}</button>
                </form>
            @endif
        @endcan
    </x-admin.card>
@endif
