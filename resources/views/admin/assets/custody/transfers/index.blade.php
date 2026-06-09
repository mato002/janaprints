<x-admin-layout :title="__('Branch Transfers')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Branch Transfers')]]">
    <x-admin.page-header :title="__('Branch Asset Transfers')" :description="__('Branch-to-branch transfers with approval and acceptance.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\AssetBranchTransfer::class)
                <a href="{{ route('admin.assets.custody.transfers.create') }}" class="erp-btn-primary">{{ __('New Transfer') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Transfer No') }}</th>
                        <th>{{ __('Asset') }}</th>
                        <th>{{ __('From Branch') }}</th>
                        <th>{{ __('To Branch') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Requested') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                        <tr>
                            <td><a href="{{ route('admin.assets.custody.transfers.show', $transfer) }}" class="erp-link font-mono">{{ $transfer->transfer_no }}</a></td>
                            <td>{{ $transfer->asset?->asset_name }}</td>
                            <td>{{ $transfer->fromBranch?->name }}</td>
                            <td>{{ $transfer->toBranch?->name }}</td>
                            <td><x-admin.status-badge :variant="$transfer->status->badgeVariant()">{{ $transfer->status->label() }}</x-admin.status-badge></td>
                            <td>{{ $transfer->requested_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No transfers yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transfers->hasPages())<div class="mt-4">{{ $transfers->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
