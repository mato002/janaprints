<x-admin-layout :title="__('Initiate Exit')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Exit Management'), 'url' => route('admin.hr.exit.dashboard')], ['label' => __('Initiate')]]">
    <x-admin.page-header :title="__('Initiate Employee Exit')" :description="__('Starts offboarding with clearance checklist and final dues calculation.')" />

    <form method="POST" action="{{ route('admin.hr.exit.store') }}" class="erp-card max-w-3xl">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="erp-label" for="employee_id">{{ __('Employee') }}</label>
                <select id="employee_id" name="employee_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select employee') }}</option>
                    @foreach ($formData['employees'] as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->full_name }} ({{ $employee->employee_number }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="exit_type">{{ __('Exit Type') }}</label>
                <select id="exit_type" name="exit_type" class="erp-input w-full" required>
                    @foreach ($formData['exitTypes'] as $type)
                        <option value="{{ $type->value }}" @selected(old('exit_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="last_working_date">{{ __('Last Working Date') }}</label>
                <input id="last_working_date" type="date" name="last_working_date" value="{{ old('last_working_date') }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label" for="exit_date">{{ __('Exit Date') }}</label>
                <input id="exit_date" type="date" name="exit_date" value="{{ old('exit_date') }}" class="erp-input w-full">
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="reason">{{ __('Reason') }}</label>
                <textarea id="reason" name="reason" rows="3" class="erp-input w-full">{{ old('reason') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="notes">{{ __('Notes') }}</label>
                <textarea id="notes" name="notes" rows="2" class="erp-input w-full">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Initiate exit') }}</button>
            <a href="{{ route('admin.hr.exit.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
