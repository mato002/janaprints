<x-admin.modal-form
    :title="__('Assign compensation')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')],
        ['label' => __('New')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.compensation.store')">
        @include('admin.hr.compensation.partials.form-fields', [
            'employee' => null,
            'compensation' => null,
            'employees' => $employees,
            'templates' => $templates,
            'paymentFrequencies' => $paymentFrequencies,
            'payrollGroups' => $payrollGroups,
        ])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create compensation') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
