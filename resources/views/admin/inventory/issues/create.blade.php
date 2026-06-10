<<<<<<< Updated upstream
<x-admin.modal-form
    :title="__('New stock issue')"
    :breadcrumbs="[['label' => __('Stock Issues'), 'url' => route('admin.inventory.issues.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.inventory.issues.store')">
        @include('admin.inventory.partials.document-header', [
            'type' => 'issue',
            'warehouses' => $warehouses,
            'destinations' => $destinations,
            'formFields' => $formFields,
            'selectedWarehouseId' => $selectedWarehouseId ?? null,
        ])
        @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'lineCount' => 5])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save draft') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
=======
<x-admin-layout :title="__('Stock issue')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Stock Issues'), 'url' => route('admin.inventory.issues.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New Stock Issue')">
        <x-slot name="actions">
            <a href="{{ route('admin.inventory.issues.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </x-slot>
    </x-admin.page-header>
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.issues.store') }}" class="space-y-4">
            @csrf
            @include('admin.inventory.partials.document-header', [
                'type' => 'issue',
                'warehouses' => $warehouses,
                'destinations' => $destinations,
                'formFields' => $formFields,
                'selectedWarehouseId' => $selectedWarehouseId ?? null,
                'productionGovernance' => $productionGovernance ?? null,
            ])
            @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'lineCount' => 5])
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Save draft') }}</button>
                <a href="{{ route('admin.inventory.issues.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
>>>>>>> Stashed changes
