@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Production\ProductionFloorDeskViews;

    $todayCount = $metrics['jobs_today'] ?? $metrics['jobs_due_today'] ?? 0;
    $completed = $metrics['jobs_completed_today'] ?? 0;
    $overdue = $metrics['jobs_overdue'] ?? 0;
    $compact = (bool) ($compact ?? false);
    $filters = $filters ?? [];
    $activeDepartment = $activeDepartment ?? null;

    $chipUrl = function (array $query) use ($activeDepartment): string {
        if ($activeDepartment) {
            $query['department'] = $activeDepartment;
        }

        return WorkspaceEmbed::url(ProductionFloorDeskViews::queueIndexUrl($activeDepartment, $query))
            ?? ProductionFloorDeskViews::queueIndexUrl($activeDepartment, $query);
    };

    $activeBucket = $filters['queue_bucket'] ?? null;
    $activeDue = $filters['due'] ?? null;
    $activeStatus = $filters['status'] ?? null;
    $hasExplicitList = filled($activeBucket) || filled($activeDue) || filled($activeStatus);

    if ($activeDue === 'overdue') {
        $activeBucket = 'overdue';
    } elseif ($activeDue === 'today') {
        $activeBucket = 'today';
    } elseif (! $hasExplicitList) {
        $activeBucket = 'today';
    }

    $chips = [
        [
            'label' => __("Today's Jobs"),
            'count' => $todayCount,
            'active' => $activeBucket === 'today',
            'url' => $chipUrl(['queue_bucket' => 'today']),
        ],
        [
            'label' => __('Overdue Jobs'),
            'count' => $overdue,
            'active' => $activeBucket === 'overdue',
            'url' => $chipUrl(['queue_bucket' => 'overdue']),
            'danger' => (int) $overdue > 0,
        ],
        [
            'label' => __('Completed Jobs'),
            'count' => $completed,
            'active' => in_array($activeBucket, ['completed', 'completed_today'], true),
            'url' => $chipUrl(['queue_bucket' => 'completed']),
        ],
    ];
@endphp

@if ($compact)
    <div class="production-queue-kpi-chips" role="tablist" aria-label="{{ __('Job lists') }}">
        @foreach ($chips as $chip)
            <a
                href="{{ $chip['url'] }}"
                @class([
                    'production-queue-kpi-chip',
                    'production-queue-kpi-chip--active' => $chip['active'],
                    'production-queue-kpi-chip--danger' => ($chip['danger'] ?? false) && ! $chip['active'],
                ])
                data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                data-turbo-action="advance"
            >
                <span class="production-queue-kpi-chip__label">{{ $chip['label'] }}</span>
                <span class="production-queue-kpi-chip__count">{{ $chip['count'] }}</span>
            </a>
        @endforeach
    </div>
@else
    <div class="mb-3 grid grid-cols-1 gap-1.5 sm:grid-cols-3">
        @foreach ($chips as $kpi)
            <a
                href="{{ $kpi['url'] }}"
                @class([
                    'production-queue-kpi-card block no-underline',
                    'ring-1 ring-erp-primary/30' => $kpi['active'],
                ])
                data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
                data-turbo-action="advance"
            >
                <p class="production-queue-kpi-card__label">{{ $kpi['label'] }}</p>
                <p @class([
                    'production-queue-kpi-card__value',
                    'text-red-700' => ($kpi['danger'] ?? false) && ! $kpi['active'],
                ])>{{ $kpi['count'] }}</p>
            </a>
        @endforeach
    </div>
@endif
