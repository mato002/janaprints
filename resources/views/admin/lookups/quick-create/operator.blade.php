<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="4xl">
    <p class="mb-4 text-sm text-slate-600">
        {{ __('Creates an HR employee and login so they can be assigned on the production floor immediately.') }}
    </p>
    @include('admin.employees.partials.form-fields', ['employee' => null])
</x-admin.lookup-nested-form>
