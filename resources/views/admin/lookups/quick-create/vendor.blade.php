<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="4xl">
    <div class="erp-form-grid">
        @include('admin.procurement.vendors.partials.form', ['vendor' => null])
    </div>
</x-admin.lookup-nested-form>
