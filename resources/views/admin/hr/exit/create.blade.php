<x-admin.modal-form
    :title="__('Initiate exit')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Exit Management'), 'url' => route('admin.hr.exit.dashboard')],
        ['label' => __('Initiate')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.exit.store')">
        @include('admin.hr.exit.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Initiate exit') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
