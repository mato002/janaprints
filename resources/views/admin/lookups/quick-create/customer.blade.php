<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="4xl">
    @include('admin.crm.customers.form', ['customer' => null])
</x-admin.lookup-nested-form>
