<x-admin-layout :title="__('Edit Shift')" :breadcrumbs="[['label' => __('Shifts'), 'url' => route('admin.hr.attendance.dashboard', ['tab' => 'shifts'])], ['label' => __('Edit')]]">
    @include('admin.hr.shifts.form', [
        'shift' => $shift,
        'shiftTypes' => $shiftTypes,
        'companies' => $companies,
        'action' => route('admin.hr.shifts.update', $shift),
        'method' => 'PUT',
    ])
</x-admin-layout>
