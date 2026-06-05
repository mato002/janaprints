<x-admin-layout :title="__('Create Shift')" :breadcrumbs="[['label' => __('Shifts'), 'url' => route('admin.hr.shifts.index')], ['label' => __('Create')]]">
    @include('admin.hr.shifts.form', [
        'shiftTypes' => $shiftTypes,
        'companies' => $companies,
        'action' => route('admin.hr.shifts.store'),
    ])
</x-admin-layout>
