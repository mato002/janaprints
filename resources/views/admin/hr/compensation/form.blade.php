<x-admin-layout :title="__('Revise Compensation')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => $employee->full_name]]">
    <x-admin.page-header :title="__('Revise Compensation')" :description="__('Creates a new effective-dated record. Previous compensation is preserved in history.')" />

    <x-admin.form-shell :action="$action" method="PUT" class="erp-card max-w-3xl space-y-4">
        @include('admin.hr.compensation.partials.form-fields', [
            'employee' => $employee,
            'compensation' => $compensation,
            'employees' => collect(),
            'templates' => $templates,
            'paymentFrequencies' => $paymentFrequencies,
            'payrollGroups' => $payrollGroups,
        ])
        <div class="flex gap-2">
            <x-primary-button>{{ __('Save revision') }}</x-primary-button>
            <a href="{{ route('admin.hr.compensation.register') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </x-admin.form-shell>
</x-admin-layout>
