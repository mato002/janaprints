@props(['filters', 'branches', 'departments', 'jobTitles', 'employees', 'employmentStatuses'])

<x-admin.card class="mb-6">
    <form method="GET" action="{{ route('admin.hr.kpi') }}" data-turbo-frame="erp-main" class="flex flex-wrap items-end gap-3">
        @if (! empty($filters['dimension']))
            <input type="hidden" name="dimension" value="{{ $filters['dimension'] }}">
        @endif
        <div>
            <label class="text-[11px] text-slate-500" for="from_date">{{ __('From') }}</label>
            <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-input mt-1">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="to_date">{{ __('To') }}</label>
            <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-input mt-1">
        </div>
        <x-admin.consolidated-branch-select
            :branches="$branches"
            :selected="$filters['branch_id'] ?? null"
            select-class="erp-input mt-1 min-w-[10rem]"
        />
        <div>
            <label class="text-[11px] text-slate-500" for="department_id">{{ __('Department') }}</label>
            <select id="department_id" name="department_id" class="erp-input mt-1 min-w-[10rem]">
                <option value="">{{ __('All departments') }}</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="employee_id">{{ __('Employee') }}</label>
            <select id="employee_id" name="employee_id" class="erp-input mt-1 min-w-[12rem]">
                <option value="">{{ __('All employees') }}</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? null) == $employee->id)>
                        {{ $employee->full_name }} ({{ $employee->employee_number }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" class="erp-input mt-1 min-w-[10rem]">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($employmentStatuses as $status)
                    <option value="{{ $status['value'] }}" @selected(($filters['status'] ?? null) == $status['value'])>{{ $status['label'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="erp-btn-primary">{{ __('Apply filters') }}</button>
    </form>
</x-admin.card>
