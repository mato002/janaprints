<x-admin-layout :title="__('Manual Attendance')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Attendance'), 'url' => route('admin.hr.attendance.dashboard')], ['label' => __('Manual')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-3xl">
        <h2 class="text-lg font-semibold text-erp-primary mb-4">{{ __('Manual Attendance Entry') }}</h2>
        <form method="POST" action="{{ route('admin.hr.attendance.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-input-label for="employee_id" :value="__('Employee')" />
                    <select name="employee_id" id="employee_id" class="erp-select mt-1 w-full" required>
                        <option value="">{{ __('Select employee') }}</option>
                        @foreach ($formData['employees'] as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                {{ $employee->full_name }} ({{ $employee->employee_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="attendance_date" :value="__('Date')" />
                    <x-text-input id="attendance_date" name="attendance_date" type="date" class="block mt-1 w-full" :value="old('attendance_date', now()->toDateString())" required />
                </div>
                <div>
                    <x-input-label for="shift_id" :value="__('Shift')" />
                    <select name="shift_id" id="shift_id" class="erp-select mt-1 w-full">
                        <option value="">{{ __('Employee default / none') }}</option>
                        @foreach ($formData['shifts'] as $shift)
                            <option value="{{ $shift->id }}" @selected(old('shift_id') == $shift->id)>{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="clock_in_at" :value="__('Clock In')" />
                    <x-text-input id="clock_in_at" name="clock_in_at" type="datetime-local" class="block mt-1 w-full" :value="old('clock_in_at')" />
                </div>
                <div>
                    <x-input-label for="clock_out_at" :value="__('Clock Out')" />
                    <x-text-input id="clock_out_at" name="clock_out_at" type="datetime-local" class="block mt-1 w-full" :value="old('clock_out_at')" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select name="status" id="status" class="erp-select mt-1 w-full" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', 'present') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="notes" :value="__('Notes')" />
                    <textarea name="notes" id="notes" rows="3" class="erp-input mt-1 w-full">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-2">
                <x-primary-button>{{ __('Save attendance') }}</x-primary-button>
                <a href="{{ route('admin.hr.attendance.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
