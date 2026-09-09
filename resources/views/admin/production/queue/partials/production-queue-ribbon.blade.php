@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Production\ProductionFloorDeskViews;

    $departmentTabs = $departmentTabs ?? [];
    $commandMetrics = $commandMetrics ?? [];
    $filters = $filters ?? [];
    $activeDepartment = $activeDepartment ?? null;
    $indexRoute = $indexRoute ?? ProductionFloorDeskViews::queueIndexUrl($activeDepartment ?: null);

    $todayCount = $commandMetrics['jobs_today'] ?? $commandMetrics['jobs_due_today'] ?? 0;
    $overdue = $commandMetrics['jobs_overdue'] ?? 0;
    $completed = $commandMetrics['jobs_completed_today'] ?? 0;

    $chipUrl = function (array $query) use ($activeDepartment): string {
        if ($activeDepartment) {
            $query['department'] = $activeDepartment;
        }

        return WorkspaceEmbed::url(ProductionFloorDeskViews::queueIndexUrl($activeDepartment, $query))
            ?? ProductionFloorDeskViews::queueIndexUrl($activeDepartment, $query);
    };

    $requestedBucket = $filters['queue_bucket'] ?? null;
    $activeDue = $filters['due'] ?? null;
    $hasExplicitList = filled($requestedBucket) || filled($activeDue) || filled($filters['status'] ?? null);

    $activeBucket = match (true) {
        $activeDue === 'overdue' || $requestedBucket === 'overdue' => 'overdue',
        in_array($requestedBucket, ['completed', 'completed_today'], true) => 'completed',
        $activeDue === 'today' || $requestedBucket === 'today' => 'today',
        ! $hasExplicitList => 'today',
        default => $requestedBucket,
    };

    $statFilters = [
        [
            'key' => 'today',
            'label' => __("Today's Jobs"),
            'count' => $todayCount,
            'active' => $activeBucket === 'today',
            'url' => $chipUrl(['queue_bucket' => 'today']),
        ],
        [
            'key' => 'overdue',
            'label' => __('Overdue Jobs'),
            'count' => $overdue,
            'active' => $activeBucket === 'overdue',
            'url' => $chipUrl(['queue_bucket' => 'overdue']),
            'danger' => (int) $overdue > 0,
        ],
        [
            'key' => 'completed',
            'label' => __('Completed Jobs'),
            'count' => $completed,
            'active' => in_array($activeBucket, ['completed', 'completed_today'], true),
            'url' => $chipUrl(['queue_bucket' => 'completed']),
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

    <div class="production-queue-ribbon__stats production-queue-ribbon__stats--buckets" role="tablist" aria-label="{{ __('Job lists') }}">
        @foreach ($statFilters as $stat)
            <a
                href="{{ $stat['url'] }}"
                @class([
                    'production-queue-ribbon__stat',
                    'production-queue-ribbon__stat--active' => $stat['active'],
                    'production-queue-ribbon__stat--danger' => ($stat['danger'] ?? false) && ! $stat['active'],
                ])
                role="tab"
                aria-selected="{{ $stat['active'] ? 'true' : 'false' }}"
                data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                data-turbo-action="advance"
            >
                {{ $stat['label'] }}
                <strong class="tabular-nums">{{ $stat['count'] }}</strong>
            </a>
        @endforeach
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
