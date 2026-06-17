<x-admin.modal-form
    :title="__('New payroll class')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')],
        ['label' => __('Payroll classes'), 'url' => route('admin.hr.compensation.templates')],
        ['label' => __('New')],
    ]"
    maxWidth="3xl"
>
    <p class="mb-4 text-sm text-slate-500">
        {{ __('Define a reusable pay package. Assign it when onboarding employees; adjust individual allowances later.') }}
    </p>

    <x-admin.form-shell :action="route('admin.hr.compensation.templates.store')">
        @include('admin.hr.compensation.templates.partials.form-fields', ['template' => null])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create payroll class') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
