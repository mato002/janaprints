<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="4xl">
    @include('admin.inventory.warehouses.form', ['warehouse' => null])
</x-admin.lookup-nested-form>
