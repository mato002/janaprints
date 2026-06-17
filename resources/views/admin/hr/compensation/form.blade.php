<x-admin.modal-form
    :title="__('Revise Compensation')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')],
        ['label' => $employee->full_name],
    ]"
    maxWidth="3xl"
>
    <p class="mb-4 text-sm text-slate-500">
        {{ __('Creates a new effective-dated record. Previous compensation is preserved in history.') }}
    </p>

    <x-admin.form-shell :action="$action" method="PUT">
        @include('admin.hr.compensation.partials.form-fields', [
            'employee' => $employee,
            'compensation' => $compensation,
            'employees' => collect(),
            'templates' => $templates,
            'paymentFrequencies' => $paymentFrequencies,
            'payrollGroups' => $payrollGroups,
        ])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save revision') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
