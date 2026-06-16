<x-admin.modal-form
    :title="__('Assign training')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')],
        ['label' => __('Assign')],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.hr.training.assignments.store')">
        @include('admin.hr.training.assignments.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Assign') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
<x-admin-layout :title="__('Assign Training')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => __('Assign')]]">
    <x-admin.page-header :title="__('Assign Training')" />

    <form method="POST" action="{{ route('admin.hr.training.assignments.store') }}" class="erp-card max-w-2xl">
        @csrf
        <div class="grid gap-4">
            <div>
                <label class="erp-label" for="employee_id">{{ __('Employee') }}</label>
                <select id="employee_id" name="employee_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select employee') }}</option>
                    @foreach ($formData['employees'] as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="training_program_id">{{ __('Training Program') }}</label>
                <select id="training_program_id" name="training_program_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select program') }}</option>
                    @foreach ($formData['programs'] as $program)
                        <option value="{{ $program->id }}" @selected(old('training_program_id', $selectedProgramId ?? null) == $program->id)>{{ $program->title }} ({{ $program->type->label() }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="due_date">{{ __('Due Date') }}</label>
                <input id="due_date" type="date" name="due_date" value="{{ old('due_date') }}" class="erp-input w-full">
            </div>
            <div>
                <label class="erp-label" for="notes">{{ __('Notes') }}</label>
                <textarea id="notes" name="notes" rows="3" class="erp-input w-full">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Assign') }}</button>
            <a href="{{ route('admin.hr.training.assignments.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
