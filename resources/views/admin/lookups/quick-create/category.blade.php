<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="2xl">
    @include('admin.inventory.catalogue.categories.partials.form', ['category' => null])
</x-admin.lookup-nested-form>
