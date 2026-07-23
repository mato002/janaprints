@php
    $statusRaw = $overview['employment_status'] ?? ($employee->employment_status?->value ?? 'active');
    $statusLabel = ucfirst(str_replace('_', ' ', (string) $statusRaw));
    $statusTone = match ($statusRaw) {
        'active' => 'success',
        'on_leave' => 'info',
        'suspended' => 'warning',
        'terminated' => 'danger',
        default => 'neutral',
    };
    $photoUrl = $employee->photo ? asset('storage/'.$employee->photo) : null;
    $initials = collect(explode(' ', (string) $employee->full_name))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $payrollReady = (bool) ($overview['payroll_profile_complete'] ?? true);
    $jobTitle = $overview['job_title'] ?? null;
    $department = $overview['department'] ?? null;
    $branch = $overview['branch'] ?? null;
    $shiftName = $employee->shift?->name;
@endphp

<header class="employee-360__hero">
    <div class="employee-360__hero-main">
        <div class="employee-360__identity">
            <a href="{{ route('admin.employees.index') }}" class="employee-360__back" data-turbo-frame="erp-main">
                ← {{ __('Employees') }}
            </a>

            <div class="employee-360__identity-row">
                <div class="employee-360__avatar" aria-hidden="true">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="" class="employee-360__avatar-img">
                    @else
                        <span class="employee-360__avatar-initials">{{ $initials ?: '?' }}</span>
                    @endif
                </div>

                <div class="employee-360__identity-text">
                    <h1 class="employee-360__name">{{ $employee->full_name }}</h1>
                    <p class="employee-360__meta">
                        <span class="employee-360__emp-no">{{ $overview['employee_number'] }}</span>
                        @if ($jobTitle)
                            <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                            <span>{{ $jobTitle }}</span>
                        @endif
                        @if ($department)
                            <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                            <span>{{ $department }}</span>
                        @endif
                        @if ($branch)
                            <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                            <span>{{ $branch }}</span>
                        @endif
                    </p>
                    <p class="employee-360__submeta">
                        <span class="employee-360__status employee-360__status--{{ $statusTone }}">{{ $statusLabel }}</span>
                        @if ($overview['hire_date'])
                            <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                            <span>{{ __('Hired') }} {{ $overview['hire_date']->format('d M Y') }}</span>
                        @endif
                        <span class="employee-360__meta-sep" aria-hidden="true">·</span>
                        @if ($supervisor)
                            <span>{{ __('Reports to') }} {{ $supervisor->full_name }}</span>
                        @else
                            <span class="employee-360__empty-inline">{{ __('No supervisor assigned') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @include('admin.hr.employees.360.action-toolbar')
    </div>

    <div class="employee-360__ribbon" aria-label="{{ __('Employee context') }}">
        <span class="employee-360__badge employee-360__badge--{{ $statusTone }}">{{ $statusLabel }}</span>
        @if ($department)
            <span class="employee-360__badge employee-360__badge--dept">{{ $department }}</span>
        @endif
        @if ($jobTitle)
            <span class="employee-360__badge employee-360__badge--role">{{ $jobTitle }}</span>
        @endif
        @if ($branch)
            <span class="employee-360__badge employee-360__badge--branch">{{ $branch }}</span>
        @endif
        @if ($shiftName)
            <span class="employee-360__badge employee-360__badge--info">{{ $shiftName }}</span>
        @endif
        @if ($payrollReady)
            <span class="employee-360__badge employee-360__badge--ready">{{ __('Payroll Ready') }}</span>
        @else
            <span class="employee-360__badge employee-360__badge--incomplete">{{ __('Payroll Incomplete') }}</span>
        @endif
    </div>
</header>
