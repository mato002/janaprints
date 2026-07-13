<x-admin.modal-form
    :title="__('Add reorder rule')"
    :breadcrumbs="[
        ['label' => __('Reorder Configuration'), 'url' => route('admin.inventory.reorder-settings.index')],
        ['label' => __('Create')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.inventory.reorder-settings.store')">
        @include('admin.inventory.reorder-settings.partials.form')
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save configuration') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
