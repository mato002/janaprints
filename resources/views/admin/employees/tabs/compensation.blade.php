@php $comp = $employee->compensation; @endphp
<x-admin.card>
    @if ($comp)
        <div class="mb-4 flex items-center justify-between">
            <span class="erp-badge erp-badge--{{ $comp->status?->badgeClass() }}">{{ $comp->status?->label() }}</span>
            <span class="text-sm text-slate-500">{{ __('Effective') }} {{ $comp->effective_from?->format('M j, Y') }}</span>
        </div>
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-xs text-slate-500">{{ __('Basic Salary') }}</dt><dd class="font-medium">{{ number_format($comp->basic_salary, 2) }} {{ $comp->currency }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Gross Components') }}</dt><dd class="font-medium">{{ number_format($comp->grossComponents(), 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Payment Frequency') }}</dt><dd>{{ $comp->payment_frequency?->label() }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Payroll Group') }}</dt><dd>{{ $comp->payroll_group_label ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('House') }}</dt><dd>{{ number_format($comp->house_allowance, 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Transport') }}</dt><dd>{{ number_format($comp->transport_allowance, 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Medical') }}</dt><dd>{{ number_format($comp->medical_allowance, 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Risk') }}</dt><dd>{{ number_format($comp->risk_allowance, 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Responsibility') }}</dt><dd>{{ number_format($comp->responsibility_allowance, 2) }}</dd></div>
        </dl>
        @can('approve', $comp)
            @if ($comp->status === App\Enums\CompensationStatus::PendingApproval)
                <form method="POST" action="{{ route('admin.hr.compensation.approve', $comp) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Approve compensation') }}</button>
                </form>
            @endif
        @endcan
    @else
        <x-admin.empty-state icon="currency-dollar" :title="__('No active compensation')" :description="__('Assign a pay package before payroll can process this employee.')" />
        @can('create', App\Models\Hr\EmployeeCompensation::class)
            <a href="{{ route('admin.hr.compensation.edit', $employee) }}" class="erp-btn-primary mt-4 inline-flex">{{ __('Assign compensation') }}</a>
        @endcan
    @endif
</x-admin.card>
