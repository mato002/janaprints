<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="2xl" enctype="multipart/form-data">
    @include('admin.inventory.catalogue.brands.partials.form', ['brand' => null])
</x-admin.lookup-nested-form>
