<x-admin-layout :title="$company ? __('Edit company') : __('Create company')" :breadcrumbs="[['label' => __('Companies'), 'url' => route('admin.companies.index')], ['label' => $company ? __('Edit') : __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">@csrf @if($method !== 'POST') @method($method) @endif
            <div class="grid grid-cols-1 gap-4">
                <div><x-input-label for="name" :value="__('Name')" /><x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $company?->name)" required /></div>
                <div><x-input-label for="code" :value="__('Code')" /><x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $company?->code)" required /></div>
                <div><x-input-label for="email" :value="__('Email')" /><x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $company?->email)" /></div>
                <div><x-input-label for="phone" :value="__('Phone')" /><x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $company?->phone)" /></div>
                <div><x-input-label for="address" :value="__('Address')" /><textarea id="address" name="address" class="block mt-1 w-full rounded-md border-gray-300">{{ old('address', $company?->address) }}</textarea></div>
                <div class="border-t border-erp-border pt-4 space-y-3">
                    <p class="text-sm font-medium text-erp-primary">{{ __('Branding assets') }}</p>
                    @if (! empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="" class="h-12 w-12 rounded border object-contain">
                    @endif
                    <div><x-input-label for="logo" :value="__('Logo')" /><input id="logo" type="file" name="logo" accept="image/*" class="mt-1 block w-full text-sm"></div>
                    @if ($company?->logo)
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_logo" value="1"> {{ __('Remove logo') }}</label>
                    @endif
                    @if (! empty($faviconUrl))
                        <img src="{{ $faviconUrl }}" alt="" class="h-8 w-8 rounded border object-contain">
                    @endif
                    <div><x-input-label for="favicon" :value="__('Favicon')" /><input id="favicon" type="file" name="favicon" accept=".png,.ico,.svg,image/png,image/x-icon,image/svg+xml" class="mt-1 block w-full text-sm"></div>
                    @if ($company?->favicon_path)
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_favicon" value="1"> {{ __('Remove favicon') }}</label>
                    @endif
                </div>
                <label class="flex items-center gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $company?->is_active ?? true))> {{ __('Active') }}</label>
            </div>
            <div class="mt-6 flex gap-3"><x-primary-button>{{ __('Save') }}</x-primary-button><a href="{{ route('admin.companies.index') }}">{{ __('Cancel') }}</a></div>
        </form>
        @if ($company)
            @can('delete', $company)
                <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" class="mt-4" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-red-600 text-sm">{{ __('Delete') }}</button></form>
            @endcan
        @endif
    </div>
</x-admin-layout>
