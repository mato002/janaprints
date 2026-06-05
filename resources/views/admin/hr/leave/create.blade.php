<x-admin-layout :title="__('Apply for Leave')" :breadcrumbs="[['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')], ['label' => __('Apply')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.hr.leave.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-input-label for="employee_id" :value="__('Employee')" />
                    <select name="employee_id" id="employee_id" class="erp-select mt-1 w-full" required>
                        @foreach ($formData['employees'] as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id', $defaultEmployeeId) == $employee->id)>
                                {{ $employee->full_name }} ({{ $employee->employee_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="leave_type_id" :value="__('Leave Type')" />
                    <select name="leave_type_id" id="leave_type_id" class="erp-select mt-1 w-full" required>
                        @foreach ($formData['leaveTypes'] as $type)
                            <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="start_date" :value="__('Start Date')" />
                    <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full" :value="old('start_date')" required />
                </div>
                <div>
                    <x-input-label for="end_date" :value="__('End Date')" />
                    <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full" :value="old('end_date')" required />
                </div>
                <label class="flex gap-2 items-center">
                    <input type="hidden" name="is_half_day_start" value="0">
                    <input type="checkbox" name="is_half_day_start" value="1" @checked(old('is_half_day_start'))>
                    {{ __('Half day (start)') }}
                </label>
                <label class="flex gap-2 items-center">
                    <input type="hidden" name="is_half_day_end" value="0">
                    <input type="checkbox" name="is_half_day_end" value="1" @checked(old('is_half_day_end'))>
                    {{ __('Half day (end)') }}
                </label>
                <div class="md:col-span-2">
                    <x-input-label for="reason" :value="__('Reason')" />
                    <textarea name="reason" id="reason" rows="3" class="erp-input mt-1 w-full" required>{{ old('reason') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-2">
                <button type="submit" name="submit" value="1" class="erp-btn-primary">{{ __('Submit leave request') }}</button>
                <a href="{{ route('admin.hr.leave.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
