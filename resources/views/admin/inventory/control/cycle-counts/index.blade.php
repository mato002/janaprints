@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Cycle Count')],
    ];
@endphp
<x-admin-layout :title="__('Cycle Count')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Cycle count schedules')">
        @can('create', App\Models\Inventory\CycleCountSchedule::class)
            <a href="{{ route('admin.inventory.cycle-counts.create') }}" class="erp-btn-primary">{{ __('New schedule') }}</a>
        @endcan
    </x-admin.page-header>

    @if ($overdue->isNotEmpty())
        <x-admin.card class="mb-6 border-amber-200 bg-amber-50">
            <h3 class="font-medium text-amber-900 mb-2">{{ __('Overdue schedules') }}</h3>
            <ul class="text-sm space-y-1">
                @foreach ($overdue as $schedule)
                    <li>{{ $schedule->warehouse?->name }} — {{ __('Due') }} {{ $schedule->next_count_date->format('Y-m-d') }}
                        <a href="{{ route('admin.inventory.cycle-counts.show', $schedule) }}" class="text-primary-600">{{ __('View') }}</a>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    <x-admin.data-table :search-placeholder="__('Search schedules…')" export-filename="cycle-count-schedules">
        <x-slot name="head">
            <tr>
                <th>{{ __('Warehouse') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Frequency') }}</th>
                <th>{{ __('Next count') }}</th>
                <th>{{ __('Responsible') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($schedules as $schedule)
                <tr x-show="rowVisible(@js(strtolower($schedule->warehouse?->name.' '.$schedule->frequency->value)))">
                    <td>{{ $schedule->warehouse?->name }}</td>
                    <td>{{ $schedule->category?->name ?? __('All') }}</td>
                    <td>{{ ucfirst($schedule->frequency->value) }}</td>
                    <td @class(['text-amber-700 font-medium' => $schedule->isOverdue()])>{{ $schedule->next_count_date->format('Y-m-d') }}</td>
                    <td>{{ $schedule->responsibleUser?->name }}</td>
                    <td><x-admin.enum-status-badge :status="$schedule->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.cycle-counts.show', $schedule)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $schedule)
                                <x-admin.table-row-action :href="route('admin.inventory.cycle-counts.edit', $schedule)">{{ __('Edit Schedule') }}</x-admin.table-row-action>
                            @endcan
                            @can('generate', $schedule)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.cycle-counts.generate', $schedule)">{{ __('Generate Count') }}</x-admin.table-row-action>
                            @endcan
                            @can('deactivate', $schedule)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.cycle-counts.deactivate', $schedule)">{{ __('Deactivate') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="calendar" :title="__('No cycle count schedules')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$schedules" /></x-slot>
    </x-admin.data-table>

    @if ($completedCounts->isNotEmpty())
        <x-admin.card class="mt-6">
            <h3 class="font-medium mb-3">{{ __('Recent cycle counts') }}</h3>
            <ul class="text-sm space-y-1">
                @foreach ($completedCounts as $count)
                    <li>
                        <a href="{{ route('admin.inventory.stock-counts.show', $count) }}" class="text-primary-600">{{ $count->count_number }}</a>
                        — {{ $count->warehouse?->name }} · {{ $count->count_date->format('Y-m-d') }}
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif
</x-admin-layout>
