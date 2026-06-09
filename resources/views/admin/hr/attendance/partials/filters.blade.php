@props(['filters', 'formData', 'statuses' => [], 'action', 'exportAction' => null, 'canExport' => false])

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="$action" :reset-url="$action" turbo-frame="erp-main">
        @if ($canExport && $exportAction)
            <x-slot name="export">
                <x-admin.export-dropdown
                    :post-action="$exportAction"
                    :post-fields="$filters"
                    :can-export="true"
                />
            </x-slot>
        @endif

        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Date') }}">
        <select name="employee_id" class="erp-toolbar-select" aria-label="{{ __('Employee') }}">
            <option value="">{{ __('All employees') }}</option>
            @foreach ($formData['employees'] as $employee)
                <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>
                    {{ $employee->full_name }} ({{ $employee->employee_number }})
                </option>
            @endforeach
        </select>
        <select name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($formData['branches'] as $branch)
                <option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <select name="department_id" class="erp-toolbar-select" aria-label="{{ __('Department') }}">
            <option value="">{{ __('All departments') }}</option>
            @foreach ($formData['departments'] as $department)
                <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <select name="shift_id" class="erp-toolbar-select" aria-label="{{ __('Shift') }}">
            <option value="">{{ __('All shifts') }}</option>
            @foreach ($formData['shifts'] as $shift)
                <option value="{{ $shift->id }}" @selected((int) ($filters['shift_id'] ?? 0) === $shift->id)>{{ $shift->name }}</option>
            @endforeach
        </select>
        @if (! empty($statuses))
            <x-admin.status-pills
                :options="collect($statuses)->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => __('All')])->all()"
                param="status"
                :current="$filters['status'] ?? ''"
            />
        @endif
    </x-admin.index-toolbar>
</x-admin.card>
