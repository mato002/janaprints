<x-admin-layout
    :title="__('Custody Dashboard')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Custody Dashboard')],
    ]"
>
    <x-admin.page-header :title="__('Custody & Accountability Dashboard')" :description="__('Assignments, transfers, returns, and asset accountability.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\AssetHandover::class)
                <a href="{{ route('admin.assets.custody.handovers.create') }}" class="erp-btn-secondary">{{ __('New Handover') }}</a>
            @endcan
            @can('create', \App\Models\Assets\AssetBranchTransfer::class)
                <a href="{{ route('admin.assets.custody.transfers.create') }}" class="erp-btn-primary">{{ __('New Transfer') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Assigned Assets')" :value="$stats['assigned_assets']" icon="user" />
        <x-admin.kpi-widget :label="__('Unassigned Assets')" :value="$stats['unassigned_assets']" icon="chip" />
        <x-admin.kpi-widget :label="__('Overdue Returns')" :value="$stats['overdue_returns']" icon="exclamation" />
        <x-admin.kpi-widget :label="__('Pending Handovers')" :value="$stats['pending_handover_acceptance']" icon="switch-horizontal" />
        <x-admin.kpi-widget :label="__('Branch Transfers')" :value="$stats['branch_transfers']" icon="office-building" />
        <x-admin.kpi-widget :label="__('Lost Assets')" :value="$stats['lost_assets']" icon="exclamation" />
        <x-admin.kpi-widget :label="__('Damaged Assets')" :value="$stats['damaged_assets']" icon="pause" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Pending Handovers') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['pending_handovers'] as $handover)
                    <li>
                        <a href="{{ route('admin.assets.custody.handovers.show', $handover) }}" class="erp-link font-mono">{{ $handover->handover_no }}</a>
                        — {{ $handover->asset?->asset_name }}
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No pending handovers.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Pending Branch Transfers') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['pending_transfers'] as $transfer)
                    <li>
                        <a href="{{ route('admin.assets.custody.transfers.show', $transfer) }}" class="erp-link font-mono">{{ $transfer->transfer_no }}</a>
                        — {{ $transfer->asset?->asset_name }}
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No pending transfers.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Department') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_department'] as $row)
                    <li class="flex justify-between"><span>{{ __('Department #:id', ['id' => $row->department_id]) }}</span><span class="font-medium">{{ $row->count }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No department assignments.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Employee') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_employee'] as $row)
                    <li class="flex justify-between"><span>{{ __('Employee #:id', ['id' => $row->employee_id]) }}</span><span class="font-medium">{{ $row->count }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No employee assignments.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>
