<x-admin.modal-form
    :title="__('Log activity')"
    :breadcrumbs="[['label' => __('Activities'), 'url' => route('admin.commercial.activities.index')], ['label' => __('Create')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.commercial.activities.store')">
        @include('admin.commercial.activities.partials.form', ['activity' => null])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
