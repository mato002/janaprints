<x-admin.lookup-nested-form :title="$title" :action="$action">
    <x-admin.lookup-company-select :companies="$companies" select-class="erp-select mt-1 w-full" />
    <div>
        <x-input-label for="name" :value="__('Artwork type name')" />
        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required maxlength="255" />
        <p class="mt-1 text-xs text-slate-500">{{ __('Example: Die-cut label, Embroidery, Outdoor banner.') }}</p>
    </div>
    <label class="inline-flex items-center gap-2 text-sm">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
        {{ __('Active') }}
    </label>
</x-admin.lookup-nested-form>
