<x-admin.lookup-nested-form :title="$title" :action="$action">
    <x-admin.lookup-company-select :companies="$companies" select-class="erp-select mt-1 w-full" />
    <div><x-input-label for="name" :value="__('Type of work')" /><x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required /></div>
    <label class="inline-flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('Active') }}</label>
</x-admin.lookup-nested-form>
