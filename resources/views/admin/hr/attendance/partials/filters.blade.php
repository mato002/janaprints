@props(['filters', 'formData', 'statuses' => [], 'action'])

<form method="GET" action="{{ $action }}" class="erp-card mb-4">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div>
            <label class="erp-label">{{ __('Date') }}</label>
            <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="erp-input w-full text-sm" />
        </div>
        <div>
            <label class="erp-label">{{ __('Employee') }}</label>
            <select name="employee_id" class="erp-input w-full text-sm">
                <option value="">{{ __('All employees') }}</option>
                @foreach ($formData['employees'] as $employee)
                    <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>
                        {{ $employee->full_name }} ({{ $employee->employee_number }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label">{{ __('Branch') }}</label>
            <select name="branch_id" class="erp-input w-full text-sm">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($formData['branches'] as $branch)
                    <option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label">{{ __('Department') }}</label>
            <select name="department_id" class="erp-input w-full text-sm">
                <option value="">{{ __('All departments') }}</option>
                @foreach ($formData['departments'] as $department)
                    <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label">{{ __('Shift') }}</label>
            <select name="shift_id" class="erp-input w-full text-sm">
                <option value="">{{ __('All shifts') }}</option>
                @foreach ($formData['shifts'] as $shift)
                    <option value="{{ $shift->id }}" @selected((int) ($filters['shift_id'] ?? 0) === $shift->id)>{{ $shift->name }}</option>
                @endforeach
            </select>
        </div>
        @if (! empty($statuses))
            <div>
                <label class="erp-label">{{ __('Attendance Status') }}</label>
                <select name="status" class="erp-input w-full text-sm">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
    <div class="mt-3 flex flex-wrap gap-2">
        <button type="submit" class="erp-btn-primary">{{ __('Apply filters') }}</button>
        <a href="{{ $action }}" class="erp-btn-secondary">{{ __('Reset') }}</a>
    </div>
</form>
