<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="2xl">
    @include('admin.inventory.catalogue.subcategories.partials.form', ['subcategory' => null])
</x-admin.lookup-nested-form>
