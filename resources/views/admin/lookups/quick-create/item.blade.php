<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="4xl">
    @include('admin.inventory.items.partials.form', ['item' => null])
</x-admin.lookup-nested-form>
