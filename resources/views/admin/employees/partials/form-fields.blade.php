@php
    $employee = $employee ?? null;
    $fields = $formFields ?? [];
@endphp

@if (($fields['company_id']['visible'] ?? true) && auth()->user()->hasRole('Super Admin'))
    <x-admin.lookup-company-select :companies="$companies" :value="old('company_id', $employee?->company_id)" select-class="block mt-1 w-full rounded-md border-gray-300" class="mb-4" />
@elseif (! auth()->user()->hasRole('Super Admin'))
    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
@endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="employee_number" :value="__('Employee number')" />
        <x-text-input
            id="employee_number"
            type="text"
            class="block mt-1 w-full bg-slate-50 erp-ref-code"
            :value="$employee ? old('employee_number', $employee->employee_number) : ($suggestedEmployeeNumber ?? '')"
            readonly
            tabindex="-1"
            aria-readonly="true"
        />
        <p class="mt-1 text-xs text-gray-500">
            @if ($employee)
                {{ __('Assigned automatically and cannot be changed.') }}
            @else
                {{ __('Assigned automatically on save using the :prefix prefix (e.g. :prefix-0766).', [
                    'prefix' => $employeeNumberPrefix ?? 'JPEMP',
                ]) }}
            @endif
        </p>
    </div>
    @if (($fields['branch_id']['visible'] ?? true))
    <x-admin.lookup-select
        name="branch_id"
        :label="$fields['branch_id']['label'] ?? __('Branch')"
        :options="$branches"
        :value="old('branch_id', $employee?->branch_id)"
        :required="($fields['branch_id']['required'] ?? true)"
        create-route="admin.branches.quick-create"
        refresh-route="admin.lookups.branches"
        permission="branches.manage"
        :modal-title="__('Create branch')"
        option-label-key="name"
        select-class="block mt-1 w-full rounded-md border-gray-300"
        scope-company-field="company_id"
        :empty-option="false"
    />
    @endif
    @if (($fields['department_id']['visible'] ?? true))
    <x-admin.lookup-select
        name="department_id"
        :label="$fields['department_id']['label'] ?? __('Department')"
        :options="$departments"
        :value="old('department_id', $employee?->department_id)"
        create-route="admin.departments.quick-create"
        refresh-route="admin.lookups.departments"
        permission="departments.manage"
        :modal-title="__('Create department')"
        option-label-key="name"
        select-class="block mt-1 w-full rounded-md border-gray-300"
        scope-company-field="company_id"
        :placeholder="__('None')"
    />
    @endif
    @if (($fields['first_name']['visible'] ?? true))
    <div><x-input-label for="first_name" :value="$fields['first_name']['label'] ?? __('First name')" /><x-text-input id="first_name" name="first_name" class="block mt-1 w-full" :value="old('first_name', $employee?->first_name)" :required="$fields['first_name']['required'] ?? true" /></div>
    @endif
    @if (($fields['middle_name']['visible'] ?? true))
    <div><x-input-label for="middle_name" :value="$fields['middle_name']['label'] ?? __('Middle name')" /><x-text-input id="middle_name" name="middle_name" class="block mt-1 w-full" :value="old('middle_name', $employee?->middle_name)" /></div>
    @endif
    @if (($fields['last_name']['visible'] ?? true))
    <div><x-input-label for="last_name" :value="$fields['last_name']['label'] ?? __('Last name')" /><x-text-input id="last_name" name="last_name" class="block mt-1 w-full" :value="old('last_name', $employee?->last_name)" :required="$fields['last_name']['required'] ?? true" /></div>
    @endif
    @if (($fields['job_title_id']['visible'] ?? true))
    <div><x-input-label for="job_title_id" :value="$fields['job_title_id']['label'] ?? __('Job Title')" />
        <select name="job_title_id" class="erp-select mt-1 w-full">
            <option value="">{{ __('Select job title') }}</option>
            @foreach ($jobTitles as $jobTitle)
                <option value="{{ $jobTitle->id }}" @selected(old('job_title_id', $employee?->job_title_id) == $jobTitle->id)>{{ $jobTitle->title }}</option>
            @endforeach
        </select>
        @if ($employee?->designation && ! $employee?->job_title_id)
            <p class="mt-1 text-xs text-amber-700">{{ __('Legacy designation') }}: {{ $employee->designation }}</p>
        @endif
    </div>
    @endif
    @if (($fields['employment_status']['visible'] ?? true))
    <div><x-input-label for="employment_status" :value="$fields['employment_status']['label'] ?? __('Employment status')" />
        <select name="employment_status" class="block mt-1 w-full rounded-md border-gray-300" @required($fields['employment_status']['required'] ?? true)>
            @foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(old('employment_status', $employee?->employment_status?->value) === $status->value)>{{ $status->name }}</option>@endforeach
        </select></div>
    @endif
    @if (! $employee)
        <div>
            <x-input-label for="hire_date" :value="__('Hire date')" />
            <input
                id="hire_date"
                name="hire_date"
                type="date"
                class="erp-input mt-1 w-full"
                value="{{ old('hire_date', now()->toDateString()) }}"
            />
        </div>
    @else
        <div>
            <x-input-label for="hire_date" :value="__('Hire date')" />
            <input
                id="hire_date"
                name="hire_date"
                type="date"
                class="erp-input mt-1 w-full"
                value="{{ old('hire_date', $employee->hire_date?->toDateString()) }}"
            />
        </div>
    @endif
    @if (! $employee && ($payrollClasses ?? collect())->isNotEmpty())
        <div>
            <x-input-label for="salary_template_id" :value="__('Payroll class')" />
            <select id="salary_template_id" name="salary_template_id" class="erp-select mt-1 w-full">
                <option value="">{{ __('None — set salary later') }}</option>
                @foreach ($payrollClasses as $payrollClass)
                    <option
                        value="{{ $payrollClass->id }}"
                        @selected((int) old('salary_template_id') === $payrollClass->id)
                    >
                        {{ $payrollClass->name }} — {{ number_format($payrollClass->grossComponents(), 2) }} {{ $payrollClass->currency }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">
                {{ __('Applies basic salary and standard allowances from the class. Adjust per employee later in the salary register.') }}
                <a href="{{ route('admin.hr.compensation.templates') }}" class="text-erp-accent hover:underline" data-turbo-frame="erp-main" data-turbo-action="advance">{{ __('Manage payroll classes') }}</a>
            </p>
        </div>
    @elseif (! $employee && auth()->user()?->can('create', App\Models\Hr\EmployeeCompensation::class))
        <div class="md:col-span-2 rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
            {{ __('No payroll classes yet.') }}
            <a href="{{ route('admin.hr.compensation.templates.create') }}" class="font-medium text-erp-accent hover:underline">{{ __('Create a payroll class') }}</a>
            {{ __('to auto-set salary during onboarding.') }}
        </div>
    @endif
    <div>
        <x-input-label for="email" :value="__('Personal email (login & onboarding)')" />
        <input
            id="email"
            name="email"
            type="email"
            class="erp-input mt-1 w-full"
            value="{{ old('email', $employee?->email) }}"
            autocomplete="email"
            @if (! $employee) required @endif
        />
        <p class="mt-1 text-xs text-gray-500">{{ __('Used for invitations, activation, and ERP login.') }}</p>
    </div>
    <div><x-input-label for="phone" :value="__('Phone')" /><x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $employee?->phone)" /></div>
    @if (($canAssignActivationRole ?? false) && ! $employee)
        <div>
            <x-input-label for="activation_role" :value="__('System role')" />
            <select id="activation_role" name="activation_role" class="erp-select mt-1 w-full">
                <option value="">{{ __('Use job title / department / default mapping') }}</option>
                @foreach (($assignableRoles ?? collect()) as $role)
                    <option value="{{ $role->name }}" @selected(old('activation_role') === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">{{ __('Optional. Applied when the employee activates their account.') }}</p>
        </div>
    @endif
</div>

@include('admin.employees.partials.form-fields-profile', ['employee' => $employee])

<label class="flex gap-2 mt-4"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employee?->is_active ?? true))> {{ __('Active') }}</label>
