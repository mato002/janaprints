@php
    $snapshot = $dashboard['snapshot'] ?? [];
    $pipeline = $dashboard['pipeline'] ?? [];
    $urgent = $dashboard['urgent'] ?? [];
    $departmentCapacity = $dashboard['department_capacity'] ?? [];
    $machineCapacity = $dashboard['machine_capacity'] ?? [];
    $maintenanceAlerts = $dashboard['maintenance_alerts'] ?? [];
    $activity = $dashboard['activity'] ?? [];
    $quickActions = $dashboard['quick_actions'] ?? [];
@endphp

<x-admin-layout
    :title="__('Production Command Center')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Command Center')],
    ]"
>
    <x-admin.page-header
        :title="__('Production Command Center')"
        :description="__('Authoritative production intelligence — snapshot, pipeline, urgency, capacity, and live floor activity.')"
    />

    <p class="mb-4 text-xs text-slate-500">{{ __('As of') }} {{ $dashboard['as_of'] ?? now()->format('Y-m-d H:i') }}</p>

    {{-- Section 1: Production Snapshot --}}
    <section class="mb-6" aria-label="{{ __('Production Snapshot') }}">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production Snapshot') }}</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
            @foreach ($snapshot as $card)
                @if ($card['clickable'] ?? false)
                    <a href="{{ $card['url'] }}" class="block transition-opacity hover:opacity-90" data-turbo-frame="erp-main">
                        <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon'] ?? 'chart-pie'" />
                    </a>
                @else
                    <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon'] ?? 'chart-pie'" />
                @endif
            @endforeach
        </div>
    </section>

    {{-- Section 2: Production Pipeline --}}
    <section class="mb-6" aria-label="{{ __('Production Pipeline') }}">
        <x-admin.card>
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production Pipeline') }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
                @foreach ($pipeline as $stage)
                    @if ($stage['url'] ?? null)
                        <a href="{{ $stage['url'] }}" class="rounded-lg border border-erp-border bg-slate-50 px-3 py-4 text-center transition-colors hover:border-erp-accent hover:bg-white" data-turbo-frame="erp-main">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $stage['label'] }}</p>
                            <p class="mt-1 text-2xl font-bold tabular-nums text-erp-primary">{{ $stage['count'] }}</p>
                        </a>
                    @else
                        <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-4 text-center">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $stage['label'] }}</p>
                            <p class="mt-1 text-2xl font-bold tabular-nums text-erp-primary">{{ $stage['count'] }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-admin.card>
    </section>

    {{-- Section 3: Urgent Attention Center --}}
    <section class="mb-6" aria-label="{{ __('Urgent Attention Center') }}">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Urgent Attention Center') }}</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach (['overdue_jobs', 'awaiting_artwork', 'awaiting_qc', 'dispatch_due_today', 'escalated_jobs'] as $sectionKey)
                @php $section = $urgent[$sectionKey] ?? []; @endphp
                <x-admin.card>
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-erp-primary">{{ $section['title'] ?? '' }}</h3>
                        @if (! empty($section['view_all_url']))
                            <a href="{{ $section['view_all_url'] }}" class="text-xs text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('View All') }}</a>
                        @endif
                    </div>
                    @if (! empty($section['empty']))
                        <x-admin.empty-state :title="__('All clear')" :description="__('No jobs require attention in this category.')" />
                    @else
                        <ul class="divide-y divide-erp-border text-sm">
                            @foreach ($section['records'] ?? [] as $record)
                                <li class="py-2.5">
                                    @if ($record['url'] ?? null)
                                        <a href="{{ $record['url'] }}" class="font-mono font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $record['job_number'] }}</a>
                                    @else
                                        <span class="font-mono font-medium text-erp-primary">{{ $record['job_number'] }}</span>
                                    @endif
                                    <p class="mt-0.5 truncate text-slate-600">{{ $record['customer'] }}</p>
                                    <p class="mt-1 flex justify-between gap-2 text-xs text-slate-500">
                                        <span>{{ $record['status'] }}</span>
                                        <span>{{ $record['due_date'] }}</span>
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin.card>
            @endforeach
        </div>
    </section>

    @if (($maintenanceAlerts['critical_failures'] ?? 0) > 0)
        <section class="mb-6" aria-label="{{ __('Critical Maintenance Alerts') }}">
            <x-admin.card>
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-red-700">{{ __('Critical Maintenance Alerts') }}</h2>
                <p class="mb-3 text-sm text-slate-600">{{ __(':count critical maintenance work orders may block production.', ['count' => $maintenanceAlerts['critical_failures']]) }}</p>
                <ul class="divide-y divide-erp-border text-sm">
                    @foreach ($maintenanceAlerts['orders'] ?? [] as $order)
                        <li class="py-2">
                            @if ($order['url'] ?? null)
                                <a href="{{ $order['url'] }}" class="erp-link font-mono">{{ $order['work_order_no'] }}</a>
                            @else
                                <span class="font-mono">{{ $order['work_order_no'] }}</span>
                            @endif
                            — {{ $order['asset_name'] }}
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        </section>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-12">
        {{-- Section 4: Department Capacity --}}
        <section class="xl:col-span-7" aria-label="{{ __('Department Capacity') }}">
            <x-admin.card>
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Department Capacity') }}</h2>
                    @can('production.work-centers.view')
                        @if (Route::has('admin.production.work-centers.index'))
                            <a href="{{ route('admin.production.work-centers.index') }}" class="text-xs text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('Work Centers') }}</a>
                        @endif
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                                <th class="py-2 pr-3">{{ __('Department') }}</th>
                                <th class="py-2 pr-3 text-right">{{ __('Active Jobs') }}</th>
                                <th class="py-2 pr-3 text-right">{{ __('Queue') }}</th>
                                <th class="py-2 pr-3 text-right">{{ __('Capacity') }}</th>
                                <th class="py-2">{{ __('Utilization') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($departmentCapacity as $dept)
                                <tr class="border-t border-erp-border {{ $dept['is_overbooked'] ? 'bg-red-50/40' : '' }}">
                                    <td class="py-2 pr-3 font-medium">{{ $dept['label'] }}</td>
                                    <td class="py-2 pr-3 text-right tabular-nums">{{ $dept['active_jobs'] }}</td>
                                    <td class="py-2 pr-3 text-right tabular-nums">{{ $dept['queue_count'] }}</td>
                                    <td class="py-2 pr-3 text-right tabular-nums">{{ $dept['capacity'] }}</td>
                                    <td class="py-2">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 min-w-[5rem] flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div
                                                    class="h-full rounded-full {{ $dept['is_overbooked'] ? 'bg-red-500' : 'bg-erp-accent' }}"
                                                    style="width: {{ min(100, $dept['utilization_percent']) }}%"
                                                ></div>
                                            </div>
                                            <span class="text-xs tabular-nums text-slate-600">{{ $dept['utilization_percent'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </section>

        {{-- Section 5: Machine Overview --}}
        <section class="xl:col-span-5" aria-label="{{ __('Machine Overview') }}">
            <x-admin.card>
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Machine Overview') }}</h2>
                <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <x-admin.kpi-widget :label="__('Available')" :value="$machineCapacity['available_count'] ?? 0" icon="check-circle" />
                    <x-admin.kpi-widget :label="__('Running')" :value="$machineCapacity['running_count'] ?? 0" icon="play" />
                    <x-admin.kpi-widget :label="__('Offline')" :value="$machineCapacity['offline_count'] ?? 0" icon="pause" />
                    <x-admin.kpi-widget :label="__('Utilization')" :value="($machineCapacity['utilization_percent'] ?? 0).'%'" icon="cog" />
                    <x-admin.kpi-widget :label="__('Availability')" :value="($machineCapacity['availability_percent'] ?? 0).'%'" icon="chart-pie" />
                    <x-admin.kpi-widget :label="__('Capacity Alerts')" :value="$machineCapacity['capacity_alerts'] ?? 0" icon="exclamation" />
                </div>
                @if (($machineCapacity['machines'] ?? []) === [])
                    <x-admin.empty-state :title="__('No machines tracked')" :description="__('Configure work centers to see machine capacity.')" />
                @else
                    <ul class="divide-y divide-erp-border text-sm">
                        @foreach ($machineCapacity['machines'] as $machine)
                            <li class="flex items-center justify-between gap-2 py-2">
                                <div class="min-w-0">
                                    @if ($machine['url'] ?? null)
                                        <a href="{{ $machine['url'] }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $machine['name'] }}</a>
                                    @else
                                        <span class="font-medium text-erp-primary">{{ $machine['name'] }}</span>
                                    @endif
                                    <p class="text-xs text-slate-500">{{ $machine['code'] }}</p>
                                    @if (! empty($machine['status']))
                                        <p class="text-xs text-slate-500">{{ $machine['status'] }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right text-xs tabular-nums text-slate-600">
                                    <p>{{ $machine['utilization_percent'] }}% {{ __('util') }}</p>
                                    <p class="{{ $machine['is_available'] ? 'text-emerald-700' : 'text-red-700' }}">
                                        {{ $machine['is_available'] ? __('Available') : __('Constrained') }}
                                    </p>
                                    @if (! empty($machine['capacity_alert']))
                                        <p class="text-amber-700">{{ __('Capacity alert') }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        </section>
    </div>

    {{-- Section 6: Today's Production Feed --}}
    <section class="mb-6" aria-label="{{ __('Today\'s Production Feed') }}">
        <x-admin.card>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __("Today's Production Feed") }}</h2>
            @if ($activity === [])
                <x-admin.empty-state :title="__('No recent activity')" :description="__('Production events will appear here as jobs move through the floor.')" />
            @else
                <ul class="divide-y divide-erp-border">
                    @foreach ($activity as $event)
                        <li class="flex flex-wrap items-start justify-between gap-2 py-3 text-sm">
                            <div class="min-w-0">
                                <p class="font-medium text-erp-primary">
                                    @if ($event['job_url'] ?? null)
                                        <a href="{{ $event['job_url'] }}" class="font-mono text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $event['job_number'] ?? '—' }}</a>
                                    @else
                                        <span class="font-mono">{{ $event['job_number'] ?? '—' }}</span>
                                    @endif
                                    <span class="text-slate-600"> — {{ $event['title'] }}</span>
                                </p>
                                @if (! empty($event['actor_name']))
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $event['actor_name'] }}</p>
                                @endif
                            </div>
                            <time class="shrink-0 text-xs tabular-nums text-slate-500" datetime="{{ $event['event_datetime'] ?? '' }}">
                                {{ $event['event_date'] ?? '' }} {{ $event['event_time'] ?? '' }}
                            </time>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </section>

    {{-- Section 7: Quick Actions --}}
    <section aria-label="{{ __('Quick Actions') }}">
        <x-admin.card>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Quick Actions') }}</h2>
            <div class="flex flex-wrap gap-2">
                @forelse ($quickActions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="{{ ! empty($action['primary']) ? 'erp-btn-primary' : 'erp-btn-secondary' }}"
                        data-turbo-frame="erp-main"
                    >{{ $action['label'] }}</a>
                @empty
                    <p class="text-sm text-slate-500">{{ __('No quick actions available for your role.') }}</p>
                @endforelse
            </div>
        </x-admin.card>
    </section>
</x-admin-layout>
