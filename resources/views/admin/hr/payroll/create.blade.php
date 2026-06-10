<x-admin.modal-form
    :title="__('New payroll run')"
    :breadcrumbs="[
        ['label' => __('Payroll'), 'url' => route('admin.hr.payroll.dashboard')],
        ['label' => __('New run')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.payroll.store')">
        @include('admin.hr.payroll.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create payroll run') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
