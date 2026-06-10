<x-admin.modal-form
    :title="__('Manual attendance')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Attendance'), 'url' => route('admin.hr.attendance.dashboard')],
        ['label' => __('Manual')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.attendance.store')">
        @include('admin.hr.attendance.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save attendance') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
