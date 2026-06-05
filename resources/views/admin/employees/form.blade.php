<x-admin-layout :title="$employee ? __('Edit employee') : __('Create employee')" :breadcrumbs="[['label' => __('Employees'), 'url' => route('admin.employees.index')], ['label' => $employee ? __('Edit') : __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ $action }}">@csrf @if($method !== 'POST') @method($method) @endif
            @if (auth()->user()->hasRole('Super Admin'))
                <div class="mb-4"><x-input-label for="company_id" :value="__('Company')" />
                    <select name="company_id" class="block mt-1 w-full rounded-md border-gray-300" required>
                        @foreach ($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id', $employee?->company_id) == $c->id)>{{ $c->name }}</option>@endforeach
                    </select></div>
            @else<input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">@endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><x-input-label for="employee_number" :value="__('Employee number')" /><x-text-input id="employee_number" name="employee_number" class="block mt-1 w-full" :value="old('employee_number', $employee?->employee_number)" required /></div>
                <div><x-input-label for="branch_id" :value="__('Branch')" />
                    <select name="branch_id" class="block mt-1 w-full rounded-md border-gray-300" required>
                        @foreach ($branches as $b)<option value="{{ $b->id }}" @selected(old('branch_id', $employee?->branch_id) == $b->id)>{{ $b->name }}</option>@endforeach
                    </select></div>
                <div><x-input-label for="department_id" :value="__('Department')" />
                    <select name="department_id" class="block mt-1 w-full rounded-md border-gray-300">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $employee?->department_id) == $d->id)>{{ $d->name }}</option>@endforeach
                    </select></div>
                <div><x-input-label for="first_name" :value="__('First name')" /><x-text-input id="first_name" name="first_name" class="block mt-1 w-full" :value="old('first_name', $employee?->first_name)" required /></div>
                <div><x-input-label for="middle_name" :value="__('Middle name')" /><x-text-input id="middle_name" name="middle_name" class="block mt-1 w-full" :value="old('middle_name', $employee?->middle_name)" /></div>
                <div><x-input-label for="last_name" :value="__('Last name')" /><x-text-input id="last_name" name="last_name" class="block mt-1 w-full" :value="old('last_name', $employee?->last_name)" required /></div>
                <div><x-input-label for="job_title_id" :value="__('Job Title')" />
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
                <div><x-input-label for="employment_status" :value="__('Employment status')" />
                    <select name="employment_status" class="block mt-1 w-full rounded-md border-gray-300" required>
                        @foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(old('employment_status', $employee?->employment_status?->value) === $status->value)>{{ $status->name }}</option>@endforeach
                    </select></div>
                <div><x-input-label for="email" :value="__('Email')" /><x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $employee?->email)" /></div>
                <div><x-input-label for="phone" :value="__('Phone')" /><x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $employee?->phone)" /></div>
            </div>
            <label class="flex gap-2 mt-4"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employee?->is_active ?? true))> {{ __('Active') }}</label>
            <div class="mt-6"><x-primary-button>{{ __('Save') }}</x-primary-button></div>
        </form>
    </div>

    @if ($employee && isset($communicationTimeline))
        @include('admin.communications.logs.partials.entity-timeline', ['logs' => $communicationTimeline, 'title' => __('Employee communication history')])
    @endif
    @if ($employee && isset($emailTimeline) && $emailTimeline->isNotEmpty())
        @include('admin.communications.logs.partials.entity-timeline', ['logs' => $emailTimeline, 'title' => __('Employee email history')])
    @endif
</x-admin-layout>
