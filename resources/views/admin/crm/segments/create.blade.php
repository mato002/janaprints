<x-admin.modal-form
    :title="__('Create segment')"
    :breadcrumbs="[['label' => __('Segments'), 'url' => route('admin.crm.segments.index')], ['label' => __('Create')]]"
    maxWidth="md"
>
    <x-admin.form-shell :action="route('admin.crm.segments.store')">
        @include('admin.crm.segments.partials.form', ['segment' => null])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Create') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
