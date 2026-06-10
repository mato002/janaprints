<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="2xl">
    @include('admin.branches.partials.form-fields', ['branch' => null, 'companies' => $companies])
</x-admin.lookup-nested-form>
