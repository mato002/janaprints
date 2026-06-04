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
            ['label' => __('Pending'), 'value' => $kpis['pending'], 'icon' => 'clock'],
            ['label' => __('Assigned'), 'value' => $kpis['assigned'], 'icon' => 'user'],
            ['label' => __('In Progress'), 'value' => $kpis['in_progress'], 'icon' => 'cog'],
            ['label' => __('Active in Queue'), 'value' => $kpis['active'], 'icon' => 'switch-horizontal'],
        ] as $kpi)
            <x-admin.kpi-widget :label="$kpi['label']" :value="(string) $kpi['value']" :icon="$kpi['icon']" />
        @endforeach
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
        <x-admin.card>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Bottleneck Detection') }}</h2>
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50/50 p-3">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-800">{{ __('Most congested work center') }}</p>
                @if ($bottlenecks['most_congested'] ?? null)
                    <p class="mt-1 text-lg font-semibold text-erp-primary">{{ $bottlenecks['most_congested']['name'] }}</p>
                    <p class="text-sm text-slate-600">{{ __(':count queue entries', ['count' => $bottlenecks['most_congested']['queue_count']]) }}</p>
                @else
                    <p class="mt-1 text-sm text-slate-500">{{ __('No congestion detected.') }}</p>
                @endif
            </div>
            @if (count($bottlenecks['overbooked'] ?? []) > 0)
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-red-700">{{ __('Overbooked work centers') }}</p>
                <ul class="divide-y divide-erp-border text-sm">
                    @foreach ($bottlenecks['overbooked'] as $center)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $center['name'] }}</span>
                            <span class="tabular-nums text-red-700">{{ $center['queue_count'] }} / {{ $center['capacity'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        <x-admin.card>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-600">{{ __('Work centers with queued jobs') }}</h2>
            <ul class="divide-y divide-erp-border text-sm">
                @forelse ($bottlenecks['with_queued_jobs'] ?? [] as $center)
                    <li class="flex items-center justify-between py-2">
                        <span>{{ $center['name'] }}</span>
                        <span class="tabular-nums text-amber-700">{{ $center['queue_count'] }}</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-slate-500">{{ __('No queued backlog.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>

    <form method="GET" action="{{ route('admin.production.queue.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[10rem] flex-1">
            <label class="text-xs text-slate-600" for="search">{{ __('Search') }}</label>
            <input
                id="search"
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                class="erp-input mt-1 w-full text-sm"
                placeholder="{{ __('Job number or customer…') }}"
            >
        </div>
        <div>
            <label class="text-xs text-slate-600" for="status">{{ __('Queue status') }}</label>
            <select id="status" name="status" class="erp-select mt-1">
                <option value="">{{ __('All') }}</option>
                @foreach (App\Enums\ProductionQueueStatus::cases() as $queueStatus)
                    <option value="{{ $queueStatus->value }}" @selected($filters['status'] === $queueStatus->value)>
                        {{ $workspace->statusLabel($queueStatus) }}
                    </option>
                @endforeach
                <option value="blocked" @selected($filters['status'] === 'blocked')>{{ __('Blocked (unassigned)') }}</option>
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-600" for="work_center_id">{{ __('Work center') }}</label>
            <select id="work_center_id" name="work_center_id" class="erp-select mt-1">
                <option value="">{{ __('All') }}</option>
                @foreach ($workCenters as $center)
                    <option value="{{ $center->id }}" @selected((string) $filters['work_center_id'] === (string) $center->id)>{{ $center->name }}</option>
                @endforeach
            </select>
        </div>
        @if ($stages->isNotEmpty())
            <div>
                <label class="text-xs text-slate-600" for="stage_id">{{ __('Stage') }}</label>
                <select id="stage_id" name="stage_id" class="erp-select mt-1">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" @selected((string) $filters['stage_id'] === (string) $stage->id)>{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label class="text-xs text-slate-600" for="date">{{ __('Updated') }}</label>
            <input id="date" type="date" name="date" value="{{ $filters['date'] }}" class="erp-input mt-1 text-sm">
        </div>
        <x-secondary-button type="submit">{{ __('Filter') }}</x-secondary-button>
        @if ($filters['status'] || $filters['work_center_id'] || $filters['stage_id'] || $filters['date'] || $filters['search'])
            <a href="{{ route('admin.production.queue.index') }}" class="text-sm text-slate-600 hover:text-erp-primary" data-turbo-frame="erp-main">{{ __('Clear') }}</a>
        @endif
    </form>

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
                            <td colspan="7" class="py-10 text-center text-slate-500">{{ __('No queue entries found.') }}</td>
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
