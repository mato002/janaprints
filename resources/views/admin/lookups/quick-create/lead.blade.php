<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="4xl">
    @include('admin.crm.leads.form', ['lead' => null])
</x-admin.lookup-nested-form>
