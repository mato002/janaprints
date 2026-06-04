<x-admin-layout
    :title="__('Document Branding')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Settings'), 'url' => route('admin.settings.show', 'hub')],
        ['label' => __('Document Branding')],
    ]"
>
    <x-admin.page-header
        :title="__('Document Branding')"
        :description="__('Upload your company logo and browser favicon for the ERP workspace.')"
    />

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.settings.branding.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <section class="space-y-4">
                <h2 class="text-sm font-semibold text-erp-primary">{{ __('Company logo') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Shown in the sidebar. Recommended: square PNG or WebP, max 2 MB.') }}</p>
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ __('Company logo') }}" class="h-16 w-16 rounded-lg border border-erp-border bg-white object-contain p-1">
                @endif
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full text-sm">
                @if ($company->logo)
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remove_logo" value="1">
                        {{ __('Remove current logo') }}
                    </label>
                @endif
                <x-input-error :messages="$errors->get('logo')" />
            </section>

            <section class="space-y-4 border-t border-erp-border pt-6">
                <h2 class="text-sm font-semibold text-erp-primary">{{ __('Favicon') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Browser tab icon. PNG, ICO, or SVG, max 1 MB.') }}</p>
                @if ($faviconUrl)
                    <img src="{{ $faviconUrl }}" alt="{{ __('Favicon') }}" class="h-8 w-8 rounded border border-erp-border bg-white object-contain p-0.5">
                @endif
                <input type="file" name="favicon" accept=".png,.ico,.svg,image/png,image/x-icon,image/svg+xml" class="block w-full text-sm">
                @if ($company->favicon_path)
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remove_favicon" value="1">
                        {{ __('Remove current favicon') }}
                    </label>
                @endif
                <x-input-error :messages="$errors->get('favicon')" />
            </section>

            <x-primary-button>{{ __('Save branding') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
