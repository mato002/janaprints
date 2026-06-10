<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="4xl">
    @include('admin.employees.partials.form-fields', ['employee' => null])
</x-admin.lookup-nested-form>
