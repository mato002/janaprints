<x-admin.modal-form
    :title="__('New price list')"
    :breadcrumbs="[
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')],
        ['label' => __('Price Lists'), 'url' => route('admin.inventory.catalogue.price-lists.index')],
        ['label' => __('Create')],
    ]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.inventory.catalogue.price-lists.store')">
        @include('admin.inventory.catalogue.price-lists.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
