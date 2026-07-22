@php
    use App\Support\Navigation\WorkspaceEmbed;

    $hubUrl = WorkspaceEmbed::url($hubUrl);
@endphp

<x-admin-layout
    :title="__('Acquisitions')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Acquisitions')],
    ]"
>
    <x-admin.page-header
        :title="__('Acquisitions')"
        :description="match ($activeTab) {
            'queue' => __('Received asset purchases awaiting capitalization.'),
            'warranties' => __('Asset warranty profiles and expiry tracking.'),
            'reconciliation' => __('Procurement, accounting, and asset register alignment.'),
            default => __('Procurement-to-asset capitalization overview.'),
        }"
    >
        @unless (WorkspaceEmbed::inWorkspaceContext())
            <x-slot name="actions">
                @if ($activeTab === 'reconciliation')
                    @can('assets.reconciliation.view')
                        <form method="POST" action="{{ route('admin.assets.acquisitions.reconciliation.store') }}">
                            @csrf
                            <button type="submit" class="erp-btn-primary">{{ __('Run reconciliation') }}</button>
                        </form>
                    @endcan
                @elseif (in_array($activeTab, ['overview', 'queue'], true))
                    @can('capitalize', \App\Models\Assets\AssetCapitalizationCandidate::class)
                        <a href="{{ WorkspaceEmbed::url($hubUrl . '?tab=queue') }}" class="erp-btn-primary" data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}">{{ __('Capitalization queue') }}</a>
                    @endcan
                @endif
            </x-slot>
        @endunless
    </x-admin.page-header>

    @include('admin.assets.acquisitions.partials.tabs-nav')

    @include('admin.assets.acquisitions.partials.tabs.' . str_replace('-', '_', $activeTab))
</x-admin-layout>
