<x-admin-layout :title="$transfer->transfer_no" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Branch Transfers'), 'url' => route('admin.assets.custody.transfers.index')], ['label' => $transfer->transfer_no]]">
    <x-admin.page-header :title="$transfer->transfer_no" :description="$transfer->asset?->asset_name">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$transfer->status->badgeVariant()">{{ $transfer->status->label() }}</x-admin.status-badge>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('Asset') }}</dt><dd><a href="{{ route('admin.assets.show', $transfer->asset) }}" class="erp-link">{{ $transfer->asset?->asset_number }}</a></dd></div>
                <div><dt class="text-slate-500">{{ __('Requested By') }}</dt><dd>{{ $transfer->requester?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('From Branch') }}</dt><dd>{{ $transfer->fromBranch?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('To Branch') }}</dt><dd>{{ $transfer->toBranch?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Condition') }}</dt><dd>{{ $transfer->condition?->label() ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Requested At') }}</dt><dd>{{ $transfer->requested_at?->format('Y-m-d H:i') }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Reason') }}</dt><dd>{{ $transfer->transfer_reason ?: '—' }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Actions') }}</h3>
            <div class="flex flex-col gap-2">
                @if ($transfer->status === \App\Enums\AssetBranchTransferStatus::PendingApproval)
                    @can('approve', $transfer)
                        <form method="POST" action="{{ route('admin.assets.custody.transfers.approve', $transfer) }}">@csrf<button type="submit" class="erp-btn-primary w-full">{{ __('Approve Transfer') }}</button></form>
                    @endcan
                @endif
                @if (in_array($transfer->status, [\App\Enums\AssetBranchTransferStatus::PendingAcceptance, \App\Enums\AssetBranchTransferStatus::Approved], true))
                    @can('manage', $transfer)
                        <form method="POST" action="{{ route('admin.assets.custody.transfers.accept', $transfer) }}">@csrf<button type="submit" class="erp-btn-primary w-full">{{ __('Accept Transfer') }}</button></form>
                        <form method="POST" action="{{ route('admin.assets.custody.transfers.reject', $transfer) }}">@csrf<button type="submit" class="erp-btn-secondary w-full">{{ __('Reject') }}</button></form>
                    @endcan
                @endif
            </div>
        </x-admin.card>
    </div>
</x-admin-layout>
