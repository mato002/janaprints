@props(['filters', 'branches', 'departments', 'jobTitles', 'employees', 'employmentStatuses'])

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="route('admin.hr.kpi')" :reset-url="route('admin.hr.kpi')">
        @if (! empty($filters['dimension']))
            <input type="hidden" name="dimension" value="{{ $filters['dimension'] }}">
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
        <select id="employee_id" name="employee_id" class="erp-toolbar-select" aria-label="{{ __('Employee') }}">
            <option value="">{{ __('All employees') }}</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? null) == $employee->id)>
                    {{ $employee->full_name }} ({{ $employee->employee_number }})
                </option>
            @endforeach
        </select>
        <x-admin.status-pills
            :options="collect($employmentStatuses)->map(fn ($status) => ['value' => $status['value'], 'label' => $status['label']])->prepend(['value' => '', 'label' => __('All statuses')])->all()"
            param="status"
            :current="$filters['status'] ?? ''"
        />
    </x-admin.index-toolbar>
</x-admin.card>
