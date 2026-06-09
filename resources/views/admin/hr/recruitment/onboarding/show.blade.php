<x-admin-layout :title="__('Onboarding')">
    <x-admin.page-header :title="__('Employee Onboarding')" :description="$onboarding->application->candidate->full_name">
        <x-slot name="actions">
            <span class="erp-badge bg-slate-100 text-slate-700">{{ $onboarding->status->label() }}</span>
            <a href="{{ route('admin.hr.recruitment.applications.show', $onboarding->application) }}" class="erp-btn-secondary text-xs">{{ __('Application') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @can('update', $onboarding)
        <form method="POST" action="{{ route('admin.hr.recruitment.onboarding.update', $onboarding) }}" class="erp-card max-w-4xl">
            @csrf
            @method('PUT')
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="erp-label" for="employee_number">{{ __('Employee Number') }}</label>
                    <input id="employee_number" type="text" name="employee_number" value="{{ old('employee_number', $onboarding->employee_number) }}" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="erp-label" for="hire_date">{{ __('Hire Date') }}</label>
                    <input id="hire_date" type="date" name="hire_date" value="{{ old('hire_date', $onboarding->hire_date?->format('Y-m-d')) }}" class="erp-input w-full">
                </div>
                <div>
                    <label class="erp-label" for="branch_id">{{ __('Branch') }}</label>
                    <select id="branch_id" name="branch_id" class="erp-input w-full">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($formData['branches'] as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id', $onboarding->branch_id) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="department_id">{{ __('Department') }}</label>
                    <select id="department_id" name="department_id" class="erp-input w-full">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($formData['departments'] as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $onboarding->department_id) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="job_title_id">{{ __('Job Title') }}</label>
                    <select id="job_title_id" name="job_title_id" class="erp-input w-full">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($formData['jobTitles'] as $jobTitle)
                            <option value="{{ $jobTitle->id }}" @selected(old('job_title_id', $onboarding->job_title_id) == $jobTitle->id)>{{ $jobTitle->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="supervisor_employee_id">{{ __('Supervisor') }}</label>
                    <select id="supervisor_employee_id" name="supervisor_employee_id" class="erp-input w-full">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($formData['employees'] as $employee)
                            <option value="{{ $employee->id }}" @selected(old('supervisor_employee_id', $onboarding->supervisor_employee_id) == $employee->id)>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="documents_collected" value="1" @checked(old('documents_collected', $onboarding->documents_collected))>
                        <span>{{ __('Documents Collected') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="system_access_granted" value="1" @checked(old('system_access_granted', $onboarding->system_access_granted))>
                        <span>{{ __('System Access Granted') }}</span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="erp-label" for="notes">{{ __('Notes') }}</label>
                    <textarea id="notes" name="notes" rows="2" class="erp-input w-full">{{ old('notes', $onboarding->notes) }}</textarea>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="erp-btn-secondary">{{ __('Save') }}</button>
            </div>
        </form>

        @if ($onboarding->status !== \App\Enums\OnboardingStatus::Completed)
            <form method="POST" action="{{ route('admin.hr.recruitment.onboarding.complete', $onboarding) }}" class="mt-4">
                @csrf
                <button type="submit" class="erp-btn-primary">{{ __('Complete & Create Employee') }}</button>
            </form>
        @endif
    @endcan

    @if ($onboarding->employee)
        <x-admin.card class="mt-6" :title="__('Created Employee')">
            <p class="text-sm">{{ $onboarding->employee->full_name }} ({{ $onboarding->employee->employee_number }})</p>
            <a href="{{ route('admin.hr.employees.show', $onboarding->employee) }}" class="erp-btn-secondary mt-3 inline-block text-xs">{{ __('Employee 360') }}</a>
        </x-admin.card>
    @endif
</x-admin-layout>
