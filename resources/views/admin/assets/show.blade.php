<x-admin-layout :title="$asset->asset_number" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.assets.index')], ['label' => $asset->asset_number]]">
    <x-admin.page-header :title="$asset->asset_name" :description="$asset->asset_number" />
    <x-admin.card class="text-sm space-y-2">
        <p><strong>{{ __('Category') }}:</strong> {{ $asset->category?->name }}</p>
        <p><strong>{{ __('Acquisition cost') }}:</strong> {{ number_format($asset->acquisition_cost, 2) }}</p>
        <p><strong>{{ __('Accumulated depreciation') }}:</strong> {{ number_format($asset->accumulated_depreciation, 2) }}</p>
        <p><strong>{{ __('Net book value') }}:</strong> {{ number_format($asset->netBookValue(), 2) }}</p>
        <p><strong>{{ __('Status') }}:</strong> {{ str($asset->status->value)->headline() }}</p>
    </x-admin.card>
</x-admin-layout>
