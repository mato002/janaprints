<x-admin.card>
    @if ($overview['is_suspended'] ?? false)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ __('This employee is suspended. ERP access is blocked and they are excluded from payroll runs.') }}
        </div>
    @elseif ($overview['access_restricted'] ?? false)
        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            {{ __('ERP access is restricted for this employee.') }}
        </div>
    @endif

    @if (! ($overview['payroll_profile_complete'] ?? true))
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-medium">{{ __('Payroll profile incomplete') }}</p>
            <p class="mt-1">{{ __('Missing: :fields', ['fields' => collect($overview['missing_payroll_fields'] ?? [])->pluck('label')->implode(', ')]) }}</p>
            @can('update', $employee)
                <a href="{{ route('admin.employees.edit', $employee) }}" class="mt-2 inline-flex text-sm font-semibold text-erp-accent hover:underline" data-erp-modal-open>
                    {{ __('Update employee profile') }}
                </a>
            @endcan
        </div>
    @endif

    <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Employment') }}</h3>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-xs text-slate-500">{{ __('Employee Number') }}</dt><dd class="erp-ref-code font-medium">{{ $overview['employee_number'] }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Name') }}</dt><dd class="font-medium">{{ $overview['name'] }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Department') }}</dt><dd>{{ $overview['department'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Branch') }}</dt><dd>{{ $overview['branch'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Job Title') }}</dt><dd>{{ $overview['job_title'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Supervisor') }}</dt><dd>{{ $supervisor?->full_name ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Employment Status') }}</dt><dd>{{ $overview['employment_status'] ? ucfirst(str_replace('_', ' ', $overview['employment_status'])) : '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Hire Date') }}</dt><dd>{{ $overview['hire_date']?->format('M j, Y') ?? '—' }}</dd></div>
    </dl>

    <h3 class="mb-3 mt-6 text-sm font-semibold text-erp-primary">{{ __('Personal & contact') }}</h3>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-xs text-slate-500">{{ __('Gender') }}</dt><dd>{{ $overview['gender'] ? ucfirst($overview['gender']) : '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Date of birth') }}</dt><dd>{{ $overview['date_of_birth']?->format('M j, Y') ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('National ID') }}</dt><dd>{{ $overview['national_id'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Personal email') }}</dt><dd>{{ $overview['email'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Phone') }}</dt><dd>{{ $overview['phone'] ?? '—' }}</dd></div>
        <div class="sm:col-span-2 lg:col-span-3"><dt class="text-xs text-slate-500">{{ __('Address') }}</dt><dd>{{ $overview['address'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Emergency contact') }}</dt><dd>{{ $overview['emergency_contact_name'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Emergency phone') }}</dt><dd>{{ $overview['emergency_contact_phone'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Next of kin') }}</dt><dd>{{ $overview['next_of_kin_name'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Next of kin phone') }}</dt><dd>{{ $overview['next_of_kin_phone'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Relationship') }}</dt><dd>{{ $overview['next_of_kin_relationship'] ?? '—' }}</dd></div>
    </dl>

    <h3 class="mb-3 mt-6 text-sm font-semibold text-erp-primary">{{ __('Statutory & payroll payment') }}</h3>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-xs text-slate-500">{{ __('KRA PIN') }}</dt><dd>{{ $overview['kra_pin'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('NSSF number') }}</dt><dd>{{ $overview['nssf_number'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('SHIF / NHIF number') }}</dt><dd>{{ $overview['nhif_number'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Bank name') }}</dt><dd>{{ $overview['bank_name'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Bank account') }}</dt><dd>{{ $overview['bank_account_number'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Branch code') }}</dt><dd>{{ $overview['bank_branch_code'] ?? '—' }}</dd></div>
    </dl>
</x-admin.card>
