<x-admin-layout :title="$handover->handover_no" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Handovers'), 'url' => route('admin.assets.custody.handovers.index')], ['label' => $handover->handover_no]]">
    <x-admin.page-header :title="$handover->handover_no" :description="$handover->asset?->asset_name">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$handover->status->badgeVariant()">{{ $handover->status->label() }}</x-admin.status-badge>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('Asset') }}</dt><dd><a href="{{ route('admin.assets.show', $handover->asset) }}" class="erp-link">{{ $handover->asset?->asset_number }}</a></dd></div>
                <div><dt class="text-slate-500">{{ __('Handover Date') }}</dt><dd>{{ $handover->handover_date?->format('Y-m-d') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('From') }}</dt><dd>{{ $handover->fromEmployee?->full_name ?? $handover->fromBranch?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('To') }}</dt><dd>{{ $handover->toEmployee?->full_name ?? $handover->toBranch?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Condition') }}</dt><dd>{{ $handover->condition?->label() ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Received Date') }}</dt><dd>{{ $handover->received_date?->format('Y-m-d') ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Condition Notes') }}</dt><dd>{{ $handover->condition_notes ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Remarks') }}</dt><dd>{{ $handover->remarks ?: '—' }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Actions') }}</h3>
            <div class="flex flex-col gap-2">
                @can('manage', $handover)
                    @if ($handover->status === \App\Enums\AssetHandoverStatus::Draft)
                        <form method="POST" action="{{ route('admin.assets.custody.handovers.submit', $handover) }}">@csrf<button type="submit" class="erp-btn-primary w-full">{{ __('Submit for Acceptance') }}</button></form>
                    @endif
                    @if ($handover->status === \App\Enums\AssetHandoverStatus::PendingAcceptance)
                        <form method="POST" action="{{ route('admin.assets.custody.handovers.accept', $handover) }}">@csrf<button type="submit" class="erp-btn-primary w-full">{{ __('Accept Handover') }}</button></form>
                        <form method="POST" action="{{ route('admin.assets.custody.handovers.reject', $handover) }}">@csrf<button type="submit" class="erp-btn-secondary w-full">{{ __('Reject') }}</button></form>
                    @endif
                @endcan
            </div>
        </x-admin.card>
    </div>
</x-admin-layout>
