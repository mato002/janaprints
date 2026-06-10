<x-admin.modal-form
    :title="__('New inventory item')"
    :breadcrumbs="[['label' => __('Items'), 'url' => route('admin.inventory.items.index')], ['label' => __('Create')]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.inventory.items.store')">
        @include('admin.inventory.items.partials.form')
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
