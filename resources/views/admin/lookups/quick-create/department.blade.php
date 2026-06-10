<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="2xl">
    @include('admin.departments.partials.form-fields', ['department' => null, 'companies' => $companies])
</x-admin.lookup-nested-form>
