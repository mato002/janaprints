<x-admin-layout :title="__('Write-Offs')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Write-Offs')]]">
    <x-admin.page-header :title="__('Asset Write-Offs')" />
    @can('manage', \App\Models\Assets\AssetWriteOff::class)
        <x-admin.card class="mb-4">
            <form method="POST" action="{{ route('admin.assets.finance.write-offs.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-3">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Asset') }}</label>
                    <select name="fixed_asset_id" class="erp-select w-full" required>
                        @foreach (\App\Models\Assets\FixedAsset::query()->forTenant()->notArchived()->orderBy('asset_name')->get(['id','asset_number','asset_name']) as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->asset_number }} — {{ $asset->asset_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Reason') }}</label>
                    <select name="reason" class="erp-select w-full" required>
                        @foreach (\App\Enums\AssetWriteOffReason::cases() as $reason)
                            <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Date') }}</label>
                    <input type="date" name="write_off_date" value="{{ now()->toDateString() }}" class="erp-input w-full" required>
                </div>
                <div class="md:col-span-3"><button class="erp-btn-primary">{{ __('Create Write-Off') }}</button></div>
            </form>
        </x-admin.card>
    @endcan
    <x-admin.card>
        <table class="erp-table w-full text-sm">
            <thead><tr><th>{{ __('No') }}</th><th>{{ __('Asset') }}</th><th>{{ __('Reason') }}</th><th>{{ __('NBV') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
                @foreach ($writeOffs as $writeOff)
                    <tr>
                        <td class="font-mono">{{ $writeOff->writeoff_no }}</td>
                        <td>{{ $writeOff->asset?->asset_number }}</td>
                        <td>{{ $writeOff->reason->label() }}</td>
                        <td>{{ number_format($writeOff->nbv_at_writeoff, 2) }}</td>
                        <td><x-admin.status-badge :variant="$writeOff->status->badgeVariant()">{{ $writeOff->status->label() }}</x-admin.status-badge></td>
                        <td class="space-x-2">
                            @if ($writeOff->status === \App\Enums\AssetWriteOffStatus::PendingApproval)
                                <form method="POST" action="{{ route('admin.assets.finance.write-offs.approve', $writeOff) }}" class="inline">@csrf<button class="erp-link">{{ __('Approve') }}</button></form>
                            @endif
                            @if ($writeOff->status === \App\Enums\AssetWriteOffStatus::Approved)
                                <form method="POST" action="{{ route('admin.assets.finance.write-offs.post', $writeOff) }}" class="inline">@csrf<button class="erp-link">{{ __('Post') }}</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($writeOffs->hasPages())<div class="mt-4">{{ $writeOffs->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
