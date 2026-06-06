@php
    $isEdit = $item->exists;
@endphp

<x-admin-layout
    :title="$isEdit ? __('Edit Gallery Item') : __('Add Gallery Item')"
    :breadcrumbs="[
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Website Content'), 'url' => route('admin.workspaces.administration.section', 'website-content')],
        ['label' => __('Gallery'), 'url' => route('admin.website.gallery.index')],
        ['label' => $isEdit ? __('Edit') : __('Create')],
    ]"
>
    <x-admin.page-header
        :title="$isEdit ? __('Edit Gallery Item') : __('Add Gallery Item')"
        :description="__('Upload project imagery and details for the public storefront gallery.')"
    />

    <x-admin.card>
        <form
            method="POST"
            action="{{ $isEdit ? route('admin.website.gallery.update', $item) : route('admin.website.gallery.store') }}"
            enctype="multipart/form-data"
            class="grid grid-cols-1 gap-5 p-6 lg:grid-cols-2"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="lg:col-span-2">
                <label class="erp-label" for="title">{{ __('Title') }}</label>
                <input id="title" name="title" type="text" class="erp-input" value="{{ old('title', $item->title) }}" required>
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="erp-label" for="category">{{ __('Category') }}</label>
                <select id="category" name="category" class="erp-input" required>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}" @selected(old('category', $item->category) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="erp-label" for="location">{{ __('Location') }}</label>
                <input id="location" name="location" type="text" class="erp-input" value="{{ old('location', $item->location) }}" placeholder="{{ __('e.g. Nairobi') }}">
            </div>

            <div>
                <label class="erp-label" for="quantity_label">{{ __('Quantity / Project Size') }}</label>
                <input id="quantity_label" name="quantity_label" type="text" class="erp-input" value="{{ old('quantity_label', $item->quantity_label) }}" placeholder="{{ __('e.g. 2,500 business cards') }}">
            </div>

            <div>
                <label class="erp-label" for="timeline_label">{{ __('Completion Timeline') }}</label>
                <input id="timeline_label" name="timeline_label" type="text" class="erp-input" value="{{ old('timeline_label', $item->timeline_label) }}" placeholder="{{ __('e.g. 3 business days') }}">
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="materials_label">{{ __('Materials Used') }}</label>
                <input id="materials_label" name="materials_label" type="text" class="erp-input" value="{{ old('materials_label', $item->materials_label) }}" placeholder="{{ __('e.g. 400gsm black core card, soft-touch laminate') }}">
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="description">{{ __('Description') }}</label>
                <textarea id="description" name="description" rows="4" class="erp-input">{{ old('description', $item->description) }}</textarea>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="outcome">{{ __('Outcome') }}</label>
                <textarea id="outcome" name="outcome" rows="3" class="erp-input" placeholder="{{ __('e.g. Delivered ahead of the client launch event.') }}">{{ old('outcome', $item->outcome) }}</textarea>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="alt_text">{{ __('Image Alt Text') }}</label>
                <input id="alt_text" name="alt_text" type="text" class="erp-input" value="{{ old('alt_text', $item->alt_text) }}" required>
                @error('alt_text')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="erp-label" for="sort_order">{{ __('Sort Order') }}</label>
                <input id="sort_order" name="sort_order" type="number" min="0" class="erp-input" value="{{ old('sort_order', $item->sort_order) }}">
                <p class="mt-1 text-xs text-slate-500">{{ __('Lower numbers appear first among featured items.') }}</p>
            </div>

            <div class="flex flex-col gap-3">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300" @checked(old('is_featured', $item->is_featured))>
                    {{ __('Featured on homepage') }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300" @checked(old('is_published', $item->is_published ?? true))>
                    {{ __('Published on public site') }}
                </label>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="image">{{ $isEdit ? __('Replace Image') : __('Project Image') }}</label>
                @if ($isEdit && $item->image_path)
                    <div class="mb-3">
                        <img src="{{ $item->publicImageUrl() }}" alt="{{ $item->alt_text }}" class="h-32 w-48 rounded-lg object-cover">
                    </div>
                @endif
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="erp-input" @unless($isEdit) required @endunless>
                @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="lg:col-span-2 flex gap-3">
                <button type="submit" class="erp-btn-primary">{{ $isEdit ? __('Save Changes') : __('Create Gallery Item') }}</button>
                <a href="{{ route('admin.website.gallery.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
