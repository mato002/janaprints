@php
    $statusRaw = $overview['employment_status'] ?? ($employee->employment_status?->value ?? 'active');
    $canUpdate = auth()->user()->can('update', $employee);
    $canCompensation = auth()->user()->can('create', App\Models\Hr\EmployeeCompensation::class);
    $canLeave = auth()->user()->can('hr.leave.view');
    $canAttendance = auth()->user()->can('hr.attendance.view');
    $canDocuments = auth()->user()->can('hr.documents.view');
    $canAssets = auth()->user()->can('assets.view') || auth()->user()->can('assets.assign');
    $canExit = auth()->user()->can('hr.exit.manage') || auth()->user()->can('hr.exit.view');
@endphp

<nav class="employee-360__actions" aria-label="{{ __('Employee actions') }}">
    <div class="employee-360__actions-primary">
        @if ($canUpdate)
            <a href="{{ route('admin.employees.edit', $employee) }}" class="erp-btn-primary employee-360__action" data-erp-modal-open>
                {{ __('Edit') }}
            </a>
        @endif
        @if ($canCompensation)
            <a href="{{ route('admin.hr.compensation.edit', $employee) }}" class="erp-btn-secondary employee-360__action">
                {{ __('Payroll') }}
            </a>
        @endif
    </div>

    <div class="employee-360__actions-secondary">
        @if ($canLeave)
            <button type="button" class="erp-btn-secondary employee-360__action" @click="setTab('leave')">{{ __('Leave') }}</button>
        @endif
        @if ($canAttendance)
            <button type="button" class="erp-btn-secondary employee-360__action" @click="setTab('attendance')">{{ __('Attendance') }}</button>
        @endif
        @if ($canDocuments)
            <button type="button" class="erp-btn-secondary employee-360__action" @click="setTab('documents')">{{ __('Documents') }}</button>
        @endif

        <details class="employee-360__more">
            <summary class="erp-btn-secondary employee-360__action employee-360__more-trigger">{{ __('More') }}</summary>
            <div class="employee-360__more-menu" role="menu">
                @if ($canAssets)
                    <button type="button" class="employee-360__more-item" role="menuitem" @click="setTab('assets')">{{ __('Assign Asset') }}</button>
                @endif
                @if ($canUpdate)
                    <a href="{{ route('admin.employees.edit', $employee) }}" class="employee-360__more-item" role="menuitem" data-erp-modal-open>{{ __('Promote / Update role') }}</a>
                @endif
                <button type="button" class="employee-360__more-item" role="menuitem" onclick="window.print()">{{ __('Print Profile') }}</button>
                @if ($overview['email'] ?? null)
                    <a href="mailto:{{ $overview['email'] }}" class="employee-360__more-item" role="menuitem">{{ __('Email') }}</a>
                @endif
                @if ($overview['phone'] ?? null)
                    <a href="sms:{{ preg_replace('/\s+/', '', (string) $overview['phone']) }}" class="employee-360__more-item" role="menuitem">{{ __('SMS') }}</a>
                @endif
                @if ($canExit && $statusRaw !== 'terminated')
                    <a href="{{ route('admin.hr.exit.create') }}" class="employee-360__more-item employee-360__more-item--danger" role="menuitem">{{ __('Terminate') }}</a>
                @endif
                <a href="{{ route('admin.employees.index') }}" class="employee-360__more-item" role="menuitem">{{ __('All employees') }}</a>
            </div>
        </details>
    </div>
</nav>
