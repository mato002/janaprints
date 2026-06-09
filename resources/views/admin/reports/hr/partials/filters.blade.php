@props(['filters', 'branches', 'departments', 'jobTitles', 'employees', 'employmentStatuses', 'can_export' => false])

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="route('admin.reports.hr')" :reset-url="route('admin.reports.hr')" turbo-frame="erp-main">
        @if ($can_export)
            <x-slot name="export">
                <x-admin.export-dropdown
                    :post-action="route('admin.reports.hr.export')"
                    :post-fields="$filters"
                    :can-export="true"
                />
            </x-slot>
        @endif

        @if (! empty($filters['tab']))
            <input type="hidden" name="tab" value="{{ $filters['tab'] }}">
        @endif
        <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        <x-admin.consolidated-branch-select
            :branches="$branches"
            :selected="$filters['branch_id'] ?? null"
            :show-label="false"
            select-class="erp-toolbar-select"
            aria-label="{{ __('Branch') }}"
        />
        <select id="department_id" name="department_id" class="erp-toolbar-select" aria-label="{{ __('Department') }}">
            <option value="">{{ __('All departments') }}</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <select id="job_title_id" name="job_title_id" class="erp-toolbar-select" aria-label="{{ __('Job title') }}">
            <option value="">{{ __('All job titles') }}</option>
            @foreach ($jobTitles as $title)
                <option value="{{ $title->id }}" @selected(($filters['job_title_id'] ?? null) == $title->id)>{{ $title->name }}</option>
            @endforeach
        </select>
        <select id="employee_id" name="employee_id" class="erp-toolbar-select" aria-label="{{ __('Employee') }}">
            <option value="">{{ __('All employees') }}</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? null) == $employee->id)>
                    {{ $employee->full_name }} ({{ $employee->employee_number }})
                </option>
            @endforeach
        </select>

        <x-slot name="secondary">
            <x-admin.status-pills
                :options="collect($employmentStatuses)->map(fn ($status) => ['value' => $status['value'], 'label' => $status['label']])->prepend(['value' => '', 'label' => __('All statuses')])->all()"
                param="status"
                :current="$filters['status'] ?? ''"
            />
        </x-slot>
    </x-admin.index-toolbar>
</x-admin.card>
