<x-admin-layout
    :title="__('Asset categories')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Categories')],
    ]"
>
    <x-admin.page-header :title="__('Asset Categories')" :description="__('Asset classification and policies.')">
        @can('create', \App\Models\Assets\AssetCategory::class)
            <x-slot name="actions">
                <a href="{{ route('admin.assets.categories.create') }}" class="erp-btn-primary">{{ __('New Category') }}</a>
            </x-slot>
        @endcan
    </x-admin.page-header>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Useful Life') }}</th>
                        <th>{{ __('Assets') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->code ?? '—' }}</td>
                            <td>{{ $category->asset_type?->label() ?? '—' }}</td>
                            <td>{{ $category->useful_life_years ?? ceil($category->useful_life_months / 12) }} {{ __('years') }}</td>
                            <td>{{ $category->assets_count }}</td>
                            <td>
                                @if ($category->is_active)
                                    <x-admin.status-badge variant="success">{{ __('Active') }}</x-admin.status-badge>
                                @else
                                    <x-admin.status-badge variant="neutral">{{ __('Inactive') }}</x-admin.status-badge>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('update', $category)
                                    <a href="{{ route('admin.assets.categories.edit', $category) }}" class="erp-link">{{ __('Edit') }}</a>
                                @endcan
                                @can('archive', $category)
                                    <form method="POST" action="{{ route('admin.assets.categories.archive', $category) }}" class="inline" onsubmit="return confirm('{{ __('Archive this category?') }}')">
                                        @csrf
                                        <button type="submit" class="erp-link text-red-600">{{ __('Archive') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-500">{{ __('No categories found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
