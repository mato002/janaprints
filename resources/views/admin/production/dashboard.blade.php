@php
    $kpis = $dashboard['kpis'] ?? [];
    $pipeline = $dashboard['pipeline'] ?? [];
    $urgent = $dashboard['urgent'] ?? [];
    $schedule = $dashboard['schedule'] ?? [];
    $workCenterLoad = $dashboard['work_center_load'] ?? [];
    $activity = $dashboard['activity'] ?? [];
    $quickActions = $dashboard['quick_actions'] ?? [];
    $performance = $dashboard['performance'] ?? [];
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
        :description="__('Operations visibility — pipeline, workload, urgency, and live activity.')"
    />

    <p class="mb-4 text-xs text-slate-500">{{ __('As of') }} {{ $dashboard['as_of'] ?? now()->format('Y-m-d H:i') }}</p>

    {{-- Section 1: Executive KPI strip --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
        @foreach ($kpis as $card)
            @if ($card['clickable'] ?? false)
                <a href="{{ $card['url'] }}" class="block transition-opacity hover:opacity-90" data-turbo-frame="erp-main">
                    <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon'] ?? 'chart-pie'" />
                </a>
            @else
                <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon'] ?? 'chart-pie'" />
            @endif
        @endforeach
    </div>

    {{-- Section 8: Performance snapshot --}}
    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.kpi-widget :label="__('Completed Today')" :value="(string) ($performance['completed_today'] ?? 0)" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Completed This Week')" :value="(string) ($performance['completed_week'] ?? 0)" icon="calendar" />
        <x-admin.kpi-widget
            :label="__('QC Pass Rate')"
            :value="$performance['qc_pass_rate'] !== null ? $performance['qc_pass_rate'].'%' : '—'"
            :hint="$performance['qc_pass_label'] ?? null"
            icon="badge-check"
        />
        <x-admin.kpi-widget :label="__('Jobs Delivered Today')" :value="(string) ($performance['delivered_today'] ?? 0)" icon="truck" />
    </div>

    {{-- Section 7: Quick actions --}}
    <x-admin.card class="mt-4">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Quick Actions') }}</h2>
        <div class="flex flex-wrap gap-2">
            @forelse ($quickActions as $action)
                <a
                    href="{{ route($action['route']) }}"
                    class="{{ ! empty($action['primary']) ? 'erp-btn-primary' : 'erp-btn-secondary' }}"
                    data-turbo-frame="erp-main"
                >{{ $action['label'] }}</a>
            @empty
                <p class="text-sm text-slate-500">{{ __('No quick actions available for your role.') }}</p>
            @endforelse
        </div>
    </x-admin.card>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
        {{-- Section 2: Pipeline --}}
        <x-admin.card class="xl:col-span-12">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production Pipeline') }}</h2>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:justify-between">
                @foreach ($pipeline as $index => $stage)
                    <div class="flex min-w-0 flex-1 flex-col items-center text-center">
                        @if ($stage['url'] ?? null)
                            <a href="{{ $stage['url'] }}" class="w-full rounded-lg border border-erp-border bg-slate-50 px-3 py-4 transition-colors hover:border-erp-accent hover:bg-white" data-turbo-frame="erp-main">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $stage['label'] }}</p>
                                <p class="mt-1 text-2xl font-bold tabular-nums text-erp-primary">{{ $stage['count'] }}</p>
                            </a>
                        @else
                            <div class="w-full rounded-lg border border-erp-border bg-slate-50 px-3 py-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $stage['label'] }}</p>
                                <p class="mt-1 text-2xl font-bold tabular-nums text-erp-primary">{{ $stage['count'] }}</p>
                            </div>
                        @endif
                        @if ($index < count($pipeline) - 1)
                            <span class="my-1 hidden text-slate-300 lg:inline" aria-hidden="true">↓</span>
                            <span class="my-1 text-slate-300 lg:hidden" aria-hidden="true">↓</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-admin.card>

        {{-- Section 3: Urgent attention --}}
        <div class="xl:col-span-12">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Urgent Attention') }}</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach (['delayed', 'awaiting_artwork', 'awaiting_qc', 'dispatch_due_today'] as $sectionKey)
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
        </div>

        {{-- Section 4: Today's schedule --}}
        <x-admin.card class="xl:col-span-7">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __("Today's Production Schedule") }}</h2>
            @if ($schedule === [])
                <x-admin.empty-state :title="__('No jobs scheduled today')" :description="__('Planned start or end dates fall outside today.')" />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                                <th class="py-2 pr-3">{{ __('Job') }}</th>
                                <th class="py-2 pr-3">{{ __('Customer') }}</th>
                                <th class="py-2 pr-3">{{ __('Work Center') }}</th>
                                <th class="py-2 pr-3">{{ __('Planned Start') }}</th>
                                <th class="py-2 pr-3">{{ __('Planned End') }}</th>
                                <th class="py-2">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedule as $row)
                                <tr class="border-t border-erp-border">
                                    <td class="py-2 pr-3">
                                        @if ($row['url'] ?? null)
                                            <a href="{{ $row['url'] }}" class="font-mono text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $row['job_number'] }}</a>
                                        @else
                                            <span class="font-mono">{{ $row['job_number'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3">{{ $row['customer'] }}</td>
                                    <td class="py-2 pr-3">{{ $row['work_center'] }}</td>
                                    <td class="py-2 pr-3 tabular-nums">{{ $row['planned_start'] }}</td>
                                    <td class="py-2 pr-3 tabular-nums">{{ $row['planned_end'] }}</td>
                                    <td class="py-2">{{ $row['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>

        {{-- Section 5: Work center load --}}
        <x-admin.card class="xl:col-span-5">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Work Center Load') }}</h2>
                @can('production.work-centers.view')
                    @if (Route::has('admin.production.work-centers.index'))
                        <a href="{{ route('admin.production.work-centers.index') }}" class="text-xs text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('View All') }}</a>
                    @endif
                @endcan
            </div>
            @if ($workCenterLoad === [])
                <x-admin.empty-state :title="__('No work centers')" :description="__('Configure work centers to see utilization.')" />
            @else
                <ul class="space-y-4">
                    @foreach ($workCenterLoad as $center)
                        <li>
                            <div class="mb-1 flex items-center justify-between gap-2 text-sm">
                                @if ($center['url'] ?? null)
                                    <a href="{{ $center['url'] }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $center['name'] }}</a>
                                @else
                                    <span class="font-medium text-erp-primary">{{ $center['name'] }}</span>
                                @endif
                                <span class="text-xs text-slate-500 tabular-nums">
                                    {{ $center['active_jobs'] }} {{ __('jobs') }} · {{ $center['queue_count'] }} {{ __('queue') }}
                                </span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full {{ ($center['utilization_percent'] ?? 0) > 100 ? 'bg-red-500' : 'bg-erp-accent' }}"
                                    style="width: {{ min(100, $center['utilization_percent'] ?? 0) }}%"
                                ></div>
                            </div>
                            <p class="mt-0.5 text-[11px] text-slate-500">{{ __('Utilization') }}: {{ $center['utilization_percent'] }}%</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        {{-- Section 6: Recent activity --}}
        <x-admin.card class="xl:col-span-12">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Recent Production Activity') }}</h2>
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
    </div>
</x-admin-layout>
