<x-admin-layout :title="__('Write-Offs')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Write-Offs')]]">
    <x-admin.page-header :title="__('Asset Write-Offs')">
        <x-slot name="actions">
            @can('manage', \App\Models\Assets\AssetWriteOff::class)
                <x-admin.form-modal-link :href="route('admin.assets.finance.write-offs.create')">
                    {{ __('Create Write-Off') }}
                </x-admin.form-modal-link>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

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
