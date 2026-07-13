<x-admin.modal-form
    :title="__('Create shift')"
    :breadcrumbs="[
        ['label' => __('Shifts'), 'url' => route('admin.hr.attendance.dashboard', ['tab' => 'shifts'])],
        ['label' => __('Create')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.shifts.store')">
        @include('admin.hr.shifts.partials.form-fields', [
            'shift' => null,
            'shiftTypes' => $shiftTypes,
            'companies' => $companies,
        ])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save shift') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
