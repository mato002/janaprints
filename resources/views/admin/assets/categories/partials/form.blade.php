<x-admin-layout
    :title="$title"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Management'), 'url' => route('admin.assets.index')],
        ['label' => $title],
    ]"
>
    <x-admin.page-header :title="$title" />
    <x-admin.card>
        <form method="POST" action="{{ $action }}" class="max-w-xl space-y-4">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif
            <div>
                <label class="text-xs text-slate-600" for="name">{{ __('Name') }}</label>
                <input id="name" name="name" class="erp-input mt-1 w-full" value="{{ old('name', $category?->name) }}" required>
            </div>
            <div>
                <x-admin.entity-code-input :record="$category" erp maxlength="30" />
            </div>
            <div>
                <label class="text-xs text-slate-600" for="asset_type">{{ __('Asset Type') }}</label>
                <select id="asset_type" name="asset_type" class="erp-select mt-1 w-full" required>
                    @foreach ($assetTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('asset_type', $category?->asset_type?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="useful_life_years">{{ __('Useful Life (years)') }}</label>
                <input id="useful_life_years" type="number" min="1" max="100" name="useful_life_years" class="erp-input mt-1 w-full" value="{{ old('useful_life_years', $category?->useful_life_years ?? 5) }}" required>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="default_gl_code">{{ __('Default GL Code') }}</label>
                <input id="default_gl_code" name="default_gl_code" class="erp-input mt-1 w-full" value="{{ old('default_gl_code', $category?->default_gl_code) }}">
            </div>
            <div>
                <label class="text-xs text-slate-600" for="depreciation_method">{{ __('Depreciation Method') }}</label>
                <input id="depreciation_method" name="depreciation_method" class="erp-input mt-1 w-full" value="{{ old('depreciation_method', $category?->depreciation_method ?? 'straight_line') }}">
            </div>
            <div>
                <label class="text-xs text-slate-600" for="description">{{ __('Description') }}</label>
                <textarea id="description" name="description" rows="3" class="erp-input mt-1 w-full">{{ old('description', $category?->description) }}</textarea>
            </div>
            @if ($category)
                <div>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                        {{ __('Active') }}
                    </label>
                </div>
            @endif
            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.assets.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
