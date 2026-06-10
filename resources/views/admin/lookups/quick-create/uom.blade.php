<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="2xl">
    @include('admin.inventory.catalogue.units.partials.form', ['unit' => null])
</x-admin.lookup-nested-form>
