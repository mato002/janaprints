<x-admin.modal-form
    :title="__('New Vacancy')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Recruitment'), 'url' => route('admin.hr.recruitment.dashboard')],
        ['label' => __('Vacancies'), 'url' => route('admin.hr.recruitment.vacancies.index')],
        ['label' => __('New')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.recruitment.vacancies.store')">
        @include('admin.hr.recruitment.partials.vacancy-form', ['formData' => $formData])

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create vacancy') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
