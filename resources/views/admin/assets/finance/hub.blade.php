<x-admin-layout
    :title="__('Finance')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Finance')],
    ]"
>
    <x-admin.page-header
        :title="__('Asset Finance')"
        :description="match ($activeTab) {
            'runs' => __('Company-wide monthly depreciation execution.'),
            'entries' => __('Posted and draft depreciation register.'),
            'reconciliation' => __('Asset register vs general ledger.'),
            'reports' => __('Register, valuation, and depreciation reports.'),
            'write-offs' => __('Damaged, lost, and obsolete asset write-offs.'),
            default => __('Fixed asset valuation, depreciation, and financial controls.'),
        }"
    >
        <x-slot name="actions">
            @if (in_array($activeTab, ['overview', 'runs'], true))
                @can('run', \App\Models\Assets\DepreciationRun::class)
                    <a href="{{ route('admin.assets.finance.runs.create') }}" class="erp-btn-primary">{{ __('New depreciation run') }}</a>
                @endcan
            @endif
            @if ($activeTab === 'write-offs')
                @can('manage', \App\Models\Assets\AssetWriteOff::class)
                    <x-admin.form-modal-link :href="route('admin.assets.finance.write-offs.create')">
                        {{ __('New write-off') }}
                    </x-admin.form-modal-link>
                @endcan
            @endif
            @if ($activeTab === 'reconciliation')
                @can('run', \App\Models\Assets\AssetRegisterReconciliation::class)
                    <form method="POST" action="{{ route('admin.assets.finance.reconciliation.store') }}">
                        @csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Run reconciliation') }}</button>
                    </form>
                @endcan
            @endif
        </x-slot>
    </x-admin.page-header>

    @include('admin.assets.finance.partials.tabs-nav')

    @include('admin.assets.finance.partials.tabs.' . str_replace('-', '_', $activeTab))
</x-admin-layout>
