<x-admin-layout
    :title="__('Custody')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Custody')],
    ]"
>
    <x-admin.page-header
        :title="__('Custody & Accountability')"
        :description="match ($activeTab) {
            'assignments' => __('Employee and department custody assignments.'),
            'handovers' => __('Formal transfer evidence between employees and branches.'),
            'returns' => __('Return workflow with condition capture.'),
            'transfers' => __('Branch-to-branch transfers with approval and acceptance.'),
            default => __('Assignments, transfers, returns, and asset accountability.'),
        }"
    >
        <x-slot name="actions">
            @if (in_array($activeTab, ['overview', 'handovers'], true))
                @can('create', \App\Models\Assets\AssetHandover::class)
                    <a href="{{ route('admin.assets.custody.handovers.create') }}" class="erp-btn-secondary" data-erp-modal-open>{{ __('New handover') }}</a>
                @endcan
            @endif
            @if (in_array($activeTab, ['overview', 'transfers'], true))
                @can('create', \App\Models\Assets\AssetBranchTransfer::class)
                    <a href="{{ route('admin.assets.custody.transfers.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New transfer') }}</a>
                @endcan
            @endif
            @if ($activeTab === 'assignments')
                @can('assets.assign')
                    <x-admin.form-modal-link :href="route('admin.assets.custody.assignments.create')">{{ __('New assignment') }}</x-admin.form-modal-link>
                @endcan
            @endif
            @if ($activeTab === 'returns')
                @can('create', \App\Models\Assets\AssetReturn::class)
                    <x-admin.form-modal-link :href="route('admin.assets.custody.returns.create')">{{ __('New return') }}</x-admin.form-modal-link>
                @endcan
            @endif
        </x-slot>
    </x-admin.page-header>

    @include('admin.assets.custody.partials.tabs-nav')

    @include('admin.assets.custody.partials.tabs.' . match ($activeTab) {
        'branch-transfers' => 'transfers',
        default => str_replace('-', '_', $activeTab),
    })
</x-admin-layout>
