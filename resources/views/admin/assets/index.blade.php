<x-admin-layout :title="__('Asset register')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.assets.dashboard')], ['label' => __('Register')]]">
    <x-admin.page-header :title="__('Asset register')">
        <x-slot name="actions">
            @can('assets.create')
                <a href="{{ route('admin.assets.create') }}" class="erp-btn-primary">{{ __('Register asset') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>
    <x-admin.card>
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Number') }}</th><th>{{ __('Name') }}</th><th>{{ __('Category') }}</th><th>{{ __('NBV') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
                @foreach ($assets as $asset)
                    <tr>
                        <td>{{ $asset->asset_number }}</td>
                        <td>{{ $asset->asset_name }}</td>
                        <td>{{ $asset->category?->name }}</td>
                        <td>{{ number_format($asset->netBookValue(), 2) }}</td>
                        <td>{{ str($asset->status->value)->headline() }}</td>
                        <td><a href="{{ route('admin.assets.show', $asset) }}" class="erp-link">{{ __('View') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $assets->links() }}</div>
    </x-admin.card>
</x-admin-layout>
