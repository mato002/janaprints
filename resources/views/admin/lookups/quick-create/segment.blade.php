<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="md">
    <x-admin.lookup-company-select :companies="$companies" select-class="erp-select mt-1 w-full" class="mb-4" />
    <div class="space-y-4">
        <div><x-input-label for="name" :value="__('Name')" /><x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required /></div>
        <div><x-input-label for="code" :value="__('Code')" /><x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code')" required /></div>
    </div>
</x-admin.lookup-nested-form>
