@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Production\ProductionFloorDeskViews;

    $departmentTabs = $departmentTabs ?? [];
    $commandMetrics = $commandMetrics ?? [];
    $filters = $filters ?? [];
    $activeDepartment = $activeDepartment ?? null;
    $indexRoute = $indexRoute ?? ProductionFloorDeskViews::queueIndexUrl($activeDepartment ?: null);

    $waiting = $commandMetrics['jobs_waiting'] ?? 0;
    $running = $commandMetrics['jobs_running'] ?? 0;
    $paused = $commandMetrics['jobs_paused'] ?? 0;
    $completed = $commandMetrics['jobs_completed_today'] ?? 0;
    $overdue = $commandMetrics['jobs_overdue'] ?? 0;
    $dueToday = $commandMetrics['jobs_due_today'] ?? 0;
    $machine = isset($commandMetrics['machine_utilisation_percent']) ? $commandMetrics['machine_utilisation_percent'].'%' : '—';
    $operator = isset($commandMetrics['operator_utilisation']) ? $commandMetrics['operator_utilisation'].'%' : '—';
    $avgHours = $commandMetrics['average_completion_hours'] ?? null;
    $visibleCount = ($summary ?? [])['total_visible'] ?? null;

    $chipUrl = function (array $query) use ($activeDepartment): string {
        $params = array_merge(['all_dates' => 1], $query);
        if ($activeDepartment) {
            $params['department'] = $activeDepartment;
        }

        return WorkspaceEmbed::url(ProductionFloorDeskViews::queueIndexUrl($activeDepartment, $params))
            ?? ProductionFloorDeskViews::queueIndexUrl($activeDepartment, $params);
    };

    $activeBucket = $filters['queue_bucket'] ?? null;
    $activeDue = $filters['due'] ?? null;
    $activeStatus = $filters['status'] ?? null;

    $statFilters = [
        [
            'label' => __('Jobs'),
            'count' => $visibleCount,
            'active' => ! filled($activeBucket) && ! filled($activeDue) && ! filled($activeStatus),
            'url' => $chipUrl([]),
        ],
        [
            'label' => __('Waiting'),
            'count' => $waiting,
            'active' => $activeBucket === 'waiting',
            'url' => $chipUrl(['queue_bucket' => 'waiting']),
        ],
        [
            'label' => __('Running'),
            'count' => $running,
            'active' => $activeBucket === 'running',
            'url' => $chipUrl(['queue_bucket' => 'running']),
        ],
        [
            'label' => __('Paused'),
            'count' => $paused,
            'active' => $activeBucket === 'paused',
            'url' => $chipUrl(['queue_bucket' => 'paused']),
        ],
        [
            'label' => __('Overdue'),
            'count' => $overdue,
            'active' => $activeDue === 'overdue',
            'url' => $chipUrl(['due' => 'overdue']),
            'danger' => (int) $overdue > 0,
        ],
    ];
@endphp

<div class="production-queue-ribbon sticky top-0 z-30 shrink-0">
    @if (count($departmentTabs) > 0)
        <nav class="production-queue-ribbon__tabs" aria-label="{{ __('Departments') }}">
            @foreach ($departmentTabs as $tab)
                <a
                    href="{{ WorkspaceEmbed::url($tab['url']) }}"
                    @class([
                        'production-queue-ribbon__tab',
                        'production-queue-ribbon__tab--'.$tab['key'] => filled($tab['key'] ?? null),
                        'production-queue-ribbon__tab--active' => $tab['active'] ?? false,
                    ])
                    data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                    data-turbo-action="advance"
                >{{ $tab['label'] }}</a>
            @endforeach
        </nav>
    @endif

    <div class="production-queue-ribbon__stats" role="group" aria-label="{{ __('Queue summary') }}">
        @foreach ($statFilters as $index => $stat)
            @if ($index > 0)
                <span class="production-queue-ribbon__stat-sep" aria-hidden="true">•</span>
            @endif
            <a
                href="{{ $stat['url'] }}"
                @class([
                    'production-queue-ribbon__stat',
                    'production-queue-ribbon__stat--active' => $stat['active'],
                    'production-queue-ribbon__stat--danger' => ($stat['danger'] ?? false) && ! $stat['active'],
                ])
                data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                data-turbo-action="advance"
            >
                {{ $stat['label'] }}
                @if ($stat['count'] !== null)
                    <strong class="tabular-nums">{{ $stat['count'] }}</strong>
                @endif
            </a>
        @endforeach

        <span class="production-queue-ribbon__stat-sep hidden sm:inline" aria-hidden="true">•</span>
        <span class="production-queue-ribbon__stat-meta hidden sm:inline">
            {{ __('Due today') }} <strong class="tabular-nums">{{ $dueToday }}</strong>
        </span>
        <span class="production-queue-ribbon__stat-sep hidden md:inline" aria-hidden="true">•</span>
        <span class="production-queue-ribbon__stat-meta hidden md:inline">
            {{ __('Machine') }} <strong>{{ $machine }}</strong>
        </span>
        <span class="production-queue-ribbon__stat-sep hidden lg:inline" aria-hidden="true">•</span>
        <span class="production-queue-ribbon__stat-meta hidden lg:inline">
            {{ __('Operator') }} <strong>{{ $operator }}</strong>
        </span>
        @if (filled($avgHours) && $avgHours !== '—')
            <span class="production-queue-ribbon__stat-sep hidden xl:inline" aria-hidden="true">•</span>
            <span class="production-queue-ribbon__stat-meta hidden xl:inline">
                {{ __('Avg') }} <strong>{{ $avgHours }}h</strong>
            </span>
        @endif
    </div>

    <div class="production-queue-ribbon__filters">
        @include('admin.production.queue.partials.toolbar', [
            'indexRoute' => $indexRoute,
            'filters' => $filters,
            'workCenters' => $workCenters,
            'operators' => $operators,
            'machines' => $machines,
            'customers' => $customers,
            'workspace' => $workspace,
            'activeDepartment' => $activeDepartment,
        ])
    </div>
</div>
