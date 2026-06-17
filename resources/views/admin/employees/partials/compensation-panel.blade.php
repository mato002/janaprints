@php
    $compensation = $employee->compensation;
@endphp

<div class="mt-8 rounded-lg border border-gray-200 bg-gray-50 p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">{{ __('Salary & compensation') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('Pay package used when payroll runs are generated.') }}</p>
        </div>
        @can('viewAny', App\Models\Hr\EmployeeCompensation::class)
            <a
                href="{{ url()->route('admin.hr.compensation.register', ['coverage' => 'missing']) }}"
                class="text-sm font-medium text-erp-accent hover:underline"
                data-turbo="false"
            >
                {{ __('Missing salaries') }}
            </a>
        @endcan
    </div>

    @if ($compensation)
        <dl class="mt-4 grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
            <div>
                <dt class="text-gray-500">{{ __('Basic salary') }}</dt>
                <dd class="font-medium text-gray-900">{{ number_format($compensation->basic_salary, 2) }} {{ $compensation->currency ?? 'KES' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Gross package') }}</dt>
                <dd class="font-medium text-gray-900">{{ number_format($compensation->grossComponents(), 2) }} {{ $compensation->currency ?? 'KES' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Effective from') }}</dt>
                <dd class="font-medium text-gray-900">{{ $compensation->effective_from?->format('M j, Y') ?? '—' }}</dd>
            </div>
        </dl>

        <div class="mt-4 flex flex-wrap gap-2">
            @can('viewAny', App\Models\Hr\EmployeeCompensation::class)
                <a href="{{ url()->route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'compensation']) }}" class="erp-btn-secondary" data-turbo="false">{{ __('View details') }}</a>
            @endcan
            @can('create', App\Models\Hr\EmployeeCompensation::class)
                <a href="{{ url()->route('admin.hr.compensation.edit', $employee) }}" class="erp-btn-secondary" data-turbo="false">{{ __('Revise salary') }}</a>
            @endcan
        </div>
    @else
        <p class="mt-4 text-sm text-amber-700">{{ __('No salary on file. Payroll cannot calculate payslips for this employee until compensation is assigned.') }}</p>
        @can('create', App\Models\Hr\EmployeeCompensation::class)
            <a href="{{ url()->route('admin.hr.compensation.edit', $employee) }}" class="erp-btn-primary mt-4 inline-flex" data-turbo="false">{{ __('Set salary') }}</a>
        @endcan
    @endif
</div>
