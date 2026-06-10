<x-admin.modal-form
    :title="__('Edit item')"
    :breadcrumbs="[['label' => __('Items'), 'url' => route('admin.inventory.items.index')], ['label' => $item->item_name]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.inventory.items.update', $item)" method="PUT">
        @include('admin.inventory.items.partials.form', ['item' => $item])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
