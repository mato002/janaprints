<x-admin.modal-form
    :title="__('Edit segment')"
    :breadcrumbs="[['label' => __('Segments'), 'url' => route('admin.crm.segments.index')], ['label' => __('Edit')]]"
    maxWidth="md"
>
    <x-admin.form-shell :action="route('admin.crm.segments.update', $segment)" method="PUT">
        @include('admin.crm.segments.partials.form', ['segment' => $segment])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
