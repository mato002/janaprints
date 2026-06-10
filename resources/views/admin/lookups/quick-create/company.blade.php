<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="2xl">
    @include('admin.companies.partials.form-fields', ['company' => null, 'showBranding' => false])
</x-admin.lookup-nested-form>
