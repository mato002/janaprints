<x-admin-layout
    :title="__('Machines')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Machines')],
    ]"
>
    <x-admin.page-header :title="__('Machines')" :description="__('Production-capable machine assets.')">
        <x-slot name="actions">
            @can('viewAny', \App\Models\Assets\MachineProfile::class)
                <a href="{{ route('admin.assets.machines.dashboard') }}" class="erp-btn-secondary">{{ __('Machine Dashboard') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[12rem] flex-1">
                <label class="erp-label">{{ __('Search') }}</label>
                <input type="search" name="search" value="{{ $filters['search'] }}" class="erp-input w-full" placeholder="{{ __('Name, code, type…') }}">
            </div>
            <div>
                <label class="erp-label">{{ __('Status') }}</label>
                <select name="status" class="erp-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Type') }}</label>
                <input type="text" name="machine_type" value="{{ $filters['machine_type'] }}" class="erp-input" placeholder="{{ __('Offset, Digital…') }}">
            </div>
            <button type="submit" class="erp-btn-secondary">{{ __('Filter') }}</button>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Machine') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Work Center') }}</th>
                        <th class="text-right">{{ __('Utilization') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($machines as $profile)
                        <tr>
                            <td>
                                <a href="{{ route('admin.assets.machines.show', $profile->fixed_asset_id) }}" class="erp-link font-medium">
                                    {{ $profile->asset?->asset_name }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $profile->asset?->asset_number }}</p>
                            </td>
                            <td class="font-mono">{{ $profile->machine_code }}</td>
                            <td>{{ $profile->machine_type }}</td>
                            <td>
                                <x-admin.status-badge :variant="$profile->production_status->badgeVariant()">
                                    {{ $profile->production_status->label() }}
                                </x-admin.status-badge>
                            </td>
                            <td>{{ $profile->workCenter?->name ?? '—' }}</td>
                            <td class="text-right tabular-nums">{{ $profile->capacity_metrics['current_utilization'] ?? 0 }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">{{ __('No production machines yet. Activate a machine profile from the asset register.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($machines->hasPages())
            <div class="mt-4">{{ $machines->links() }}</div>
        @endif
    </x-admin.card>
</x-admin-layout>
