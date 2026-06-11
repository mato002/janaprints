<x-admin-layout
    :title="__('Edit Media Slot')"
    :breadcrumbs="[
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Website Content'), 'url' => route('admin.workspaces.administration.section', 'website-content')],
        ['label' => __('Media Library'), 'url' => route('admin.website.media.index')],
        ['label' => __('Edit')],
    ]"
>
    <x-admin.page-header
        :title="$item->label ?? $item->slot_key"
        :description="$usageLabel"
    />

    <x-admin.card class="mb-4">
        <div class="flex flex-wrap items-center gap-3 p-4">
            <x-admin.status-badge :variant="$sourceStatus['variant']">{{ $sourceStatus['label'] }}</x-admin.status-badge>
            <span class="text-sm text-slate-600">{{ $sourceStatus['detail'] }}</span>
        </div>
    </x-admin.card>

    <x-admin.card>
        <form
            method="POST"
            action="{{ route('admin.website.media.update', $item) }}"
            enctype="multipart/form-data"
            class="grid grid-cols-1 gap-5 p-6 lg:grid-cols-2"
        >
            @csrf
            @method('PUT')

            <div class="lg:col-span-2">
                <label class="erp-label">{{ __('Slot Key') }}</label>
                <input type="text" class="erp-input bg-slate-50 font-mono text-sm" value="{{ $item->slot_key }}" readonly disabled>
            </div>

            <div>
                <label class="erp-label" for="section">{{ __('Section') }}</label>
                <input id="section" type="text" class="erp-input bg-slate-50" value="{{ $sections[$item->section] ?? $item->section }}" readonly disabled>
            </div>

            <div>
                <label class="erp-label" for="label">{{ __('Admin Label') }}</label>
                <input id="label" name="label" type="text" class="erp-input" value="{{ old('label', $item->label) }}">
                @error('label')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="alt_text">{{ __('Image Alt Text') }} <span class="text-red-500">*</span></label>
                <input id="alt_text" name="alt_text" type="text" class="erp-input" value="{{ old('alt_text', $item->alt_text) }}" required>
                <p class="mt-1 text-xs text-slate-500">{{ __('Required for accessibility and SEO on the public storefront.') }}</p>
                @error('alt_text')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="erp-label" for="sort_order">{{ __('Sort Order') }}</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="9999" class="erp-input" value="{{ old('sort_order', $item->sort_order) }}">
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $item->is_active))>
                    {{ __('Active on storefront') }}
                </label>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="image">{{ __('Replace Image') }}</label>
                <div class="mt-2 flex flex-wrap items-start gap-4">
                    <div class="overflow-hidden rounded-lg border border-slate-200">
                        <img
                            src="{{ $item->previewUrl() }}"
                            alt="{{ $item->alt_text }}"
                            class="h-32 w-48 object-cover"
                            onerror="this.onerror=null;this.src='{{ asset('images/storefront/facility/production-floor.jpg') }}';"
                        >
                    </div>
                    <div class="min-w-0 flex-1">
                        <input id="image" name="image" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="erp-input">
                        <p class="mt-1 text-xs text-slate-500">{{ __('JPG, PNG or WebP. Max 5 MB. Stored in website-media/ on the public disk.') }}</p>
                        @if ($item->fallback_path)
                            <p class="mt-2 text-xs text-slate-500">{{ __('Config fallback:') }} <code class="text-xs">{{ $item->fallback_path }}</code></p>
                        @endif
                        @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="erp-btn-primary">{{ __('Save Media Slot') }}</button>
                <a href="{{ route('admin.website.media.index', ['section' => $item->section]) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
                @if ($item->hasUploadedImage())
                    @can('update', $item)
                        <button
                            type="submit"
                            form="remove-media-image"
                            class="erp-btn-secondary text-red-600"
                            onclick="return confirm(@js(__('Remove uploaded image and revert to config fallback?')))"
                        >
                            {{ __('Remove Uploaded Image') }}
                        </button>
                    @endcan
                @endif
            </div>
        </form>

        @if ($item->hasUploadedImage())
            @can('update', $item)
                <form id="remove-media-image" method="POST" action="{{ route('admin.website.media.remove-image', $item) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="confirm" value="1">
                </form>
            @endcan
        @endif
    </x-admin.card>
</x-admin-layout>
