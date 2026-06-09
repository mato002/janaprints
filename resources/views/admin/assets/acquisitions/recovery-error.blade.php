<x-admin-layout :title="__('Posting Error')" :breadcrumbs="[
    ['label' => __('Capitalization Recovery Queue'), 'url' => route('admin.assets.acquisitions.recovery.index')],
    ['label' => $asset->asset_number],
]">
    <x-admin.page-header :title="__('Posting Error')" :description="$asset->asset_name" />

    <x-admin.card>
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-slate-500">{{ __('Asset') }}</dt>
                <dd><a href="{{ route('admin.assets.show', $asset) }}" class="erp-link">{{ $asset->asset_number }} — {{ $asset->asset_name }}</a></dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Capitalized By') }}</dt>
                <dd>{{ $asset->capitalizationCandidate?->capitalizer?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Status') }}</dt>
                <dd>
                    <x-admin.status-badge :variant="$asset->acquisitionAccountingStatus()->badgeVariant()">
                        {{ $asset->acquisitionAccountingStatus()->label() }}
                    </x-admin.status-badge>
                </dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Reason / Error') }}</dt>
                <dd class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-800">{{ $reason }}</dd>
            </div>
        </dl>

        <div class="mt-4 flex gap-2">
            <a href="{{ route('admin.assets.acquisitions.recovery.index') }}" class="erp-btn-secondary">{{ __('Back to Queue') }}</a>
            @can('retryCapitalizationRecovery', $asset)
                <form method="POST" action="{{ route('admin.assets.acquisitions.recovery.retry', $asset) }}">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Retry Posting') }}</button>
                </form>
            @endcan
        </div>
    </x-admin.card>
</x-admin-layout>
