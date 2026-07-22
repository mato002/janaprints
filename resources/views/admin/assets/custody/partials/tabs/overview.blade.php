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
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="text-sm font-semibold">{{ __('Pending Handovers') }}</h3>
            <a href="{{ $hubUrl }}?tab=handovers" class="text-xs text-erp-accent hover:underline" data-turbo-frame="module-workspace-content">{{ __('View all') }}</a>
        </div>
        <ul class="space-y-2 text-sm">
            @forelse ($stats['pending_handovers'] as $handover)
                <li>
                    <a href="{{ route('admin.assets.custody.handovers.show', $handover['id']) }}" class="erp-link font-mono">{{ $handover['handover_no'] }}</a>
                    — {{ $handover['asset_name'] }}
                </li>
            @empty
                <li class="text-slate-500">{{ __('No pending handovers.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>

    <x-admin.card>
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="text-sm font-semibold">{{ __('Pending Branch Transfers') }}</h3>
            <a href="{{ $hubUrl }}?tab=transfers" class="text-xs text-erp-accent hover:underline" data-turbo-frame="module-workspace-content">{{ __('View all') }}</a>
        </div>
        <ul class="space-y-2 text-sm">
            @forelse ($stats['pending_transfers'] as $transfer)
                <li>
                    <a href="{{ route('admin.assets.custody.transfers.show', $transfer['id']) }}" class="erp-link font-mono">{{ $transfer['transfer_no'] }}</a>
                    — {{ $transfer['asset_name'] }}
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
                <li class="flex justify-between"><span>{{ $row['department_name'] }}</span><span class="font-medium">{{ $row['count'] }}</span></li>
            @empty
                <li class="text-slate-500">{{ __('No department assignments.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Employee') }}</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($stats['by_employee'] as $row)
                <li class="flex justify-between"><span>{{ $row['employee_name'] }}</span><span class="font-medium">{{ $row['count'] }}</span></li>
            @empty
                <li class="text-slate-500">{{ __('No employee assignments.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
</div>
