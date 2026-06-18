<x-admin.modal-form
    :title="__('New Assignment')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Assignments'), 'url' => route('admin.assets.custody.assignments.index')],
        ['label' => __('New Assignment')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.assets.custody.assignments.store')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="erp-label" for="fixed_asset_id">{{ __('Asset') }}</label>
                <select id="fixed_asset_id" name="fixed_asset_id" class="erp-select w-full" required>
                    <option value="">{{ __('Select asset…') }}</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected(old('fixed_asset_id') == $asset->id)>{{ $asset->asset_number }} — {{ $asset->asset_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="assignment_type">{{ __('Assignment Type') }}</label>
                <select id="assignment_type" name="assignment_type" class="erp-select w-full" required>
                    <option value="employee" @selected(old('assignment_type', 'employee') === 'employee')>{{ __('Employee') }}</option>
                    <option value="department" @selected(old('assignment_type') === 'department')>{{ __('Department') }}</option>
                </select>
            </div>
            <div>
                <label class="erp-label" for="expected_return_date">{{ __('Expected Return') }}</label>
                <input type="date" id="expected_return_date" name="expected_return_date" value="{{ old('expected_return_date') }}" class="erp-input w-full">
            </div>
            <div>
                <label class="erp-label" for="assigned_to_employee_id">{{ __('Employee') }}</label>
                <select id="assigned_to_employee_id" name="assigned_to_employee_id" class="erp-select w-full">
                    <option value="">{{ __('Select employee…') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('assigned_to_employee_id') == $employee->id)>{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="assigned_to_department_id">{{ __('Department') }}</label>
                <select id="assigned_to_department_id" name="assigned_to_department_id" class="erp-select w-full">
                    <option value="">{{ __('Select department…') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('assigned_to_department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="assignment_reason">{{ __('Reason') }}</label>
                <input type="text" id="assignment_reason" name="assignment_reason" value="{{ old('assignment_reason') }}" class="erp-input w-full" maxlength="120">
            </div>
        </div>
        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Assign Asset') }}</button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
