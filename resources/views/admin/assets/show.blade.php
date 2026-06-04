<x-admin-layout :title="$asset->asset_number" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.assets.index')], ['label' => $asset->asset_number]]">
    <x-admin.page-header :title="$asset->asset_name" :description="$asset->asset_number">
        <x-slot name="actions">
            <a href="{{ route('admin.assets.barcode', $asset) }}" class="erp-btn-secondary" target="_blank">{{ __('Barcode label') }}</a>
            @can('assets.manage')
                <a href="{{ route('admin.assets.transfer', $asset) }}" class="erp-btn-secondary">{{ __('Transfer') }}</a>
                <a href="{{ route('admin.assets.dispose', $asset) }}" class="erp-btn-secondary">{{ __('Dispose') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>
    <x-admin.card class="text-sm space-y-2">
        <p><strong>{{ __('Category') }}:</strong> {{ $asset->category?->name }}</p>
        <p><strong>{{ __('Barcode') }}:</strong> {{ $asset->barcode ?? $asset->asset_number }}</p>
        <p><strong>{{ __('Acquisition cost') }}:</strong> {{ number_format($asset->acquisition_cost, 2) }}</p>
        <p><strong>{{ __('Accumulated depreciation') }}:</strong> {{ number_format($asset->accumulated_depreciation, 2) }}</p>
        <p><strong>{{ __('Net book value') }}:</strong> {{ number_format($asset->netBookValue(), 2) }}</p>
        <p><strong>{{ __('Status') }}:</strong> {{ str($asset->status->value)->headline() }}</p>
    </x-admin.card>
    @can('assets.manage')
        <x-admin.card class="mt-4 flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.assets.repair', $asset) }}">@csrf<button class="erp-btn-secondary">{{ __('Mark under repair') }}</button></form>
            <form method="POST" action="{{ route('admin.assets.repair-complete', $asset) }}">@csrf<button class="erp-btn-secondary">{{ __('Complete repair') }}</button></form>
            <form method="POST" action="{{ route('admin.assets.depreciate', $asset) }}" class="flex items-center gap-2">
                @csrf
                <input type="date" name="period_date" value="{{ now()->endOfMonth()->toDateString() }}" class="erp-input" required>
                <button class="erp-btn-primary">{{ __('Run depreciation') }}</button>
            </form>
        </x-admin.card>
        <x-admin.card class="mt-4">
            <h3 class="text-sm font-semibold">{{ __('Schedule maintenance') }}</h3>
            <form method="POST" action="{{ route('admin.assets.maintenance', $asset) }}" class="mt-3 flex flex-wrap gap-2">
                @csrf
                <input type="text" name="maintenance_type" class="erp-input" placeholder="{{ __('Type') }}" required>
                <input type="date" name="scheduled_date" class="erp-input">
                <input type="text" name="description" class="erp-input min-w-[12rem]" placeholder="{{ __('Description') }}">
                <x-primary-button>{{ __('Schedule') }}</x-primary-button>
            </form>
        </x-admin.card>
    @endcan
    @if ($asset->maintenances->isNotEmpty())
        <x-admin.card class="mt-4">
            <h3 class="mb-2 text-sm font-semibold">{{ __('Maintenance history') }}</h3>
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Type') }}</th><th>{{ __('Scheduled') }}</th><th>{{ __('Status') }}</th><th>{{ __('Cost') }}</th></tr></thead>
                <tbody>
                    @foreach ($asset->maintenances as $m)
                        <tr>
                            <td>{{ $m->maintenance_type }}</td>
                            <td>{{ $m->scheduled_date?->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $m->status }}</td>
                            <td>{{ number_format($m->cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @endif
</x-admin-layout>
