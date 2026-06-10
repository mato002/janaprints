@php
    use App\Support\Navigation\WorkspaceEmbed;

    $embedded = WorkspaceEmbed::isEmbedded();
@endphp

<x-admin-layout
    :title="__('Document Branding')"
    :breadcrumbs="$embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ['label' => __('Document Branding')],
    ]"
    :use-workspace-navigation="! $embedded"
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
            </section>

            <section class="space-y-4 border-t border-erp-border pt-8">
                <h2 class="text-sm font-semibold text-erp-primary">{{ __('Browser favicon') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Shown in browser tabs. Recommended: ICO or PNG, max 512 KB.') }}</p>
                @if ($faviconUrl)
                    <img src="{{ $faviconUrl }}" alt="{{ __('Favicon') }}" class="h-8 w-8 rounded border border-erp-border bg-white object-contain p-0.5">
                @endif
                <input type="file" name="favicon" accept="image/x-icon,image/png,image/vnd.microsoft.icon" class="block w-full text-sm">
            </section>

            <div class="border-t border-erp-border pt-6">
                <x-primary-button>{{ __('Save branding') }}</x-primary-button>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
