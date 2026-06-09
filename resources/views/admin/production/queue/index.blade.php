<x-admin-layout
    :title="__('Production Queue')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Production Queue')],
    ]"
>
    <x-admin.page-header
        :title="__('Production Queue')"
        :description="__('Queued operations awaiting execution across work centers.')"
    />

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['label' => __('Queued'), 'value' => $kpis['queued'], 'icon' => 'clock'],
            ['label' => __('Assigned'), 'value' => $kpis['assigned'], 'icon' => 'user'],
            ['label' => __('In Progress'), 'value' => $kpis['in_progress'], 'icon' => 'cog'],
            ['label' => __('Blocked'), 'value' => $kpis['blocked'], 'icon' => 'exclamation'],
        ] as $kpi)
            <x-admin.kpi-widget :label="$kpi['label']" :value="(string) $kpi['value']" :icon="$kpi['icon']" />
        @endforeach
    </div>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.production.queue.index')" :reset-url="route('admin.production.queue.index')">
            <input id="search" type="search" name="search" value="{{ $filters['search'] }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Job number or customer…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select id="status" name="status" class="erp-toolbar-select" aria-label="{{ __('Queue status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach (App\Enums\ProductionQueueStatus::cases() as $queueStatus)
                    <option value="{{ $queueStatus->value }}" @selected($filters['status'] === $queueStatus->value)>
                        {{ $workspace->statusLabel($queueStatus) }}
                    </option>
                @endforeach
                <option value="blocked" @selected($filters['status'] === 'blocked')>{{ __('Blocked (unassigned)') }}</option>
            </select>
            <select id="work_center_id" name="work_center_id" class="erp-toolbar-select" aria-label="{{ __('Work center') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($workCenters as $center)
                    <option value="{{ $center->id }}" @selected((string) $filters['work_center_id'] === (string) $center->id)>{{ $center->name }}</option>
                @endforeach
            </select>
            <select id="operator_id" name="operator_id" class="erp-toolbar-select" aria-label="{{ __('Operator') }}">
                <option value="">{{ __('All Operators') }}</option>
                <option value="unassigned" @selected($filters['operator_id'] === 'unassigned')>{{ __('Unassigned') }}</option>
                @foreach ($operators as $operator)
                    <option value="{{ $operator->id }}" @selected((string) $filters['operator_id'] === (string) $operator->id)>{{ $operator->name }}</option>
                @endforeach
            </select>
            @if ($stages->isNotEmpty())
                <select id="stage_id" name="stage_id" class="erp-toolbar-select" aria-label="{{ __('Stage') }}">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" @selected((string) $filters['stage_id'] === (string) $stage->id)>{{ $stage->name }}</option>
                    @endforeach
                </select>
            @endif
            <input id="date" type="date" name="date" value="{{ $filters['date'] }}" class="erp-toolbar-input" aria-label="{{ __('Updated') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card :padding="false">
        <div class="border-b border-erp-border px-4 py-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Queue Register') }}</h2>
            <p class="mt-0.5 text-xs text-slate-500">{{ __('Ordered by work center and queue position') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Position') }}</th>
                        <th>{{ __('Job Card') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Work Center') }}</th>
                        <th>{{ __('Operator') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($queues as $queue)
                        @php $row = $workspace->presentRow($queue); @endphp
                        <tr class="{{ $row['is_delayed'] ? 'bg-amber-50/40' : '' }}">
                            <td class="tabular-nums font-medium">{{ $row['queue_position'] }}</td>
                            <td class="font-mono">{{ $row['job_card_number'] }}</td>
                            <td>{{ $row['customer_name'] }}</td>
                            <td>
                                @if ($row['work_center_url'])
                                    <a href="{{ $row['work_center_url'] }}" class="text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $row['work_center_name'] }}</a>
                                @else
                                    {{ $row['work_center_name'] }}
                                @endif
                            </td>
                            <td>{{ $row['operator_name'] }}</td>
                            <td><x-admin.enum-status-badge :status="$row['status']->value" /></td>
                            <td class="erp-table-actions-col">
                                @if ($row['job_360_url'])
                                    <a href="{{ $row['job_360_url'] }}" class="text-xs font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('Open Job 360') }}</a>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state
                                    icon="switch-horizontal"
                                    :title="__('No queued jobs found')"
                                    :description="__('Adjust filters or schedule jobs into production.')"
                                >
                                    @if (auth()->user()?->can('production.view'))
                                        <x-slot:action>
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                <a href="{{ route('admin.production.job-cards.index') }}" class="erp-btn-primary text-sm" data-turbo-frame="erp-main">{{ __('View Job Cards') }}</a>
                                                @if (auth()->user()?->can('production.scheduling.view'))
                                                    <a href="{{ route('admin.production.scheduling.index') }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Open Scheduling') }}</a>
                                                @endif
                                            </div>
                                        </x-slot:action>
                                    @endif
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($queues->hasPages())
            <div class="border-t border-erp-border px-4 py-3">
                {{ $queues->links() }}
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
