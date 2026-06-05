<x-admin-layout :title="__('Warranty Center')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Warranty Center')]]">
    <x-admin.page-header :title="__('Warranty Center')" :description="__('Asset warranty profiles and expiry tracking.')" />

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Asset') }}</th>
                        <th>{{ __('Vendor') }}</th>
                        <th>{{ __('Start') }}</th>
                        <th>{{ __('End') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($warranties as $warranty)
                        <tr>
                            <td><a href="{{ route('admin.assets.show', $warranty->asset) }}" class="erp-link">{{ $warranty->asset?->asset_name }}</a></td>
                            <td>{{ $warranty->vendor?->vendor_name ?? '—' }}</td>
                            <td>{{ $warranty->warranty_start?->format('Y-m-d') }}</td>
                            <td>{{ $warranty->warranty_end?->format('Y-m-d') }}</td>
                            <td>{{ $warranty->reference_number ?? '—' }}</td>
                            <td><x-admin.status-badge :status="$warranty->status->value" :label="$warranty->status->label()" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-slate-500">{{ __('No warranty profiles.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $warranties->links() }}</div>
    </x-admin.card>
</x-admin-layout>
