<x-admin-layout :title="__('Asset categories')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.assets.dashboard')], ['label' => __('Categories')]]">
    <x-admin.page-header :title="__('Asset categories')">
        <x-slot name="actions">
            @can('assets.create')
                <a href="{{ route('admin.assets.categories.create') }}" class="erp-btn-primary">{{ __('New category') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>
    <x-admin.card>
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Code') }}</th><th>{{ __('GL') }}</th><th>{{ __('Life (mo)') }}</th><th>{{ __('Assets') }}</th></tr></thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->code }}</td>
                        <td>{{ $category->default_gl_code ?? '—' }}</td>
                        <td>{{ $category->useful_life_months }}</td>
                        <td>{{ $category->assets_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
