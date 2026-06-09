<x-admin-layout :title="__('Capitalization Recovery Queue')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Capitalization Recovery Queue')]]">
    <x-admin.page-header
        :title="__('Capitalization Recovery Queue')"
        :description="__('Capitalized procurement assets awaiting acquisition journal posting.')"
    >
        <x-slot name="actions">
            <x-admin.kpi-widget :label="__('Pending Posting')" :value="$pending_count" icon="scale" />
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()" class="mb-4">
            <input type="search" name="search" value="{{ request('search') }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search asset…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (\App\Enums\AssetAcquisitionAccountingStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>

        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Asset') }}</th>
                        <th>{{ __('Capitalization Date') }}</th>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr>
                            <td>
                                <a href="{{ route('admin.assets.show', $asset) }}" class="erp-link font-medium">{{ $asset->asset_name }}</a>
                                <div class="text-xs text-slate-500">{{ $asset->asset_number }}</div>
                            </td>
                            <td>{{ $asset->capitalization_date?->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $asset->capitalizationCandidate?->capitalizer?->name ?? '—' }}</td>
                            <td class="max-w-xs truncate" title="{{ $queue->recoveryReason($asset) }}">{{ $queue->recoveryReason($asset) }}</td>
                            <td>
                                <x-admin.status-badge :variant="$asset->acquisitionAccountingStatus()->badgeVariant()">
                                    {{ $asset->acquisitionAccountingStatus()->label() }}
                                </x-admin.status-badge>
                            </td>
                            <td class="whitespace-nowrap text-right">
                                @can('postCapitalizationRecovery', $asset)
                                    <form method="POST" action="{{ route('admin.assets.acquisitions.recovery.post', $asset) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="erp-link">{{ __('Post To GL') }}</button>
                                    </form>
                                @endcan
                                @can('retryCapitalizationRecovery', $asset)
                                    <form method="POST" action="{{ route('admin.assets.acquisitions.recovery.retry', $asset) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="erp-link">{{ __('Retry') }}</button>
                                    </form>
                                @endcan
                                @can('viewCapitalizationRecoveryError', $asset)
                                    <a href="{{ route('admin.assets.acquisitions.recovery.error', $asset) }}" class="erp-link">{{ __('View Error') }}</a>
                                @endcan
                                @can('viewCapitalizationRecoveryAudit', $asset)
                                    <a href="{{ route('admin.assets.acquisitions.recovery.audit', $asset) }}" class="erp-link">{{ __('Audit History') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-slate-500">{{ __('No capitalized assets are waiting for GL posting.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assets->links() }}</div>
    </x-admin.card>
</x-admin-layout>
