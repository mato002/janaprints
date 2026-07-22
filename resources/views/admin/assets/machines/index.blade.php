@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp
<x-admin-layout
    :title="__('Machines')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Machines')],
    ]"
>
    <x-admin.page-header
        :title="__('Machines')"
        :description="__('Production machines — availability, utilization, and floor assignments.')"
    >
        @can('create', \App\Models\Assets\MachineProfile::class)
            <x-slot name="actions">
                <a href="{{ route('admin.assets.machines.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Add machine') }}</a>
            </x-slot>
        @endcan
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach ($summary as $card)
            @php
                $cardFilter = $card['filter'] ?? [];
                $statusFilter = $filters['status'] ?? null;
                $isActive = ($card['key'] === 'total' && blank($statusFilter))
                    || (isset($cardFilter['status']) && $statusFilter === $cardFilter['status']);
                $summaryUrl = WorkspaceEmbed::url(route('admin.assets.machines.index', WorkspaceEmbed::queryParams([
                    'search' => $filters['search'] ?? null,
                    'machine_type' => $filters['machine_type'] ?? null,
                    ...$cardFilter,
                ])));
            @endphp
            <a
                href="{{ $summaryUrl }}"
                class="block transition-opacity hover:opacity-90"
                data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                data-turbo-action="advance"
            >
                <x-admin.kpi-widget
                    :label="strtoupper($card['label'])"
                    :value="$card['value'] . ($card['key'] === 'utilization' ? '%' : '')"
                    :hint="$card['hint']"
                    :class="'machines-board__card' . ($isActive ? ' machines-board__card--active' : '')"
                />
            </a>
        @endforeach
    </div>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="route('admin.assets.machines.index')">
            <input type="search" name="search" value="{{ $filters['search'] }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Name, code, type…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}" data-erp-auto-submit>
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="search" name="machine_type" value="{{ $filters['machine_type'] }}" class="erp-toolbar-input" placeholder="{{ __('Offset, Digital…') }}" aria-label="{{ __('Type') }}" data-erp-auto-search>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :searchable="false"
        export-filename="machines"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Machine') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Work center') }}</th>
                <th scope="col" class="text-right">{{ __('Utilization') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($machines as $profile)
                <tr>
                    <td>
                        <span class="font-medium">{{ $profile->asset?->asset_name }}</span>
                        <p class="text-xs text-slate-500">{{ $profile->machine_code }} · {{ $profile->asset?->asset_number }}</p>
                    </td>
                    <td>{{ $profile->machine_type }}</td>
                    <td>
                        <x-admin.status-badge :variant="$profile->production_status->badgeVariant()">
                            {{ $profile->production_status->label() }}
                        </x-admin.status-badge>
                    </td>
                    <td>{{ $profile->workCenter?->name ?? '—' }}</td>
                    <td class="text-right tabular-nums">{{ $profile->capacity_metrics['current_utilization'] ?? 0 }}%</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.assets.machines.show', $profile->asset)">{{ __('Open') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state icon="clipboard-list" :title="__('No production machines yet')" :description="__('Add a machine here, or activate one from the asset register.')">
                            @can('create', \App\Models\Assets\MachineProfile::class)
                                <x-slot name="action">
                                    <a href="{{ route('admin.assets.machines.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Add machine') }}</a>
                                </x-slot>
                            @endcan
                        </x-admin.empty-state>
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$machines" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
