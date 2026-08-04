@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Production\ProductionFloorDeskViews;

    $waiting = $metrics['jobs_waiting'] ?? 0;
    $running = $metrics['jobs_running'] ?? 0;
    $paused = $metrics['jobs_paused'] ?? 0;
    $completed = $metrics['jobs_completed_today'] ?? 0;
    $overdue = $metrics['jobs_overdue'] ?? 0;
    $dueToday = $metrics['jobs_due_today'] ?? 0;
    $machine = isset($metrics['machine_utilisation_percent']) ? $metrics['machine_utilisation_percent'].'%' : '—';
    $operator = isset($metrics['operator_utilisation']) ? $metrics['operator_utilisation'].'%' : '—';
    $avgHours = $metrics['average_completion_hours'] ?? null;
    $compact = (bool) ($compact ?? false);
    $summary = $summary ?? [];
    $visibleCount = $summary['total_visible'] ?? null;
    $filters = $filters ?? [];
    $activeDepartment = $activeDepartment ?? null;

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

    $chips = [
        [
            'label' => __('All'),
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
            'label' => __('Completed'),
            'count' => $completed,
            'active' => $activeBucket === 'completed_today',
            'url' => $chipUrl(['queue_bucket' => 'completed_today']),
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

@if ($compact)
    <div class="production-queue-kpi-chips" role="group" aria-label="{{ __('Queue summary') }}">
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
                @if ($chip['count'] !== null)
                    <span class="production-queue-kpi-chip__count">{{ $chip['count'] }}</span>
                @endif
            </a>
        @endforeach

        <span class="production-queue-kpi-chips__meta" aria-label="{{ __('Utilisation') }}">
            <span>{{ __('Due today') }} <strong>{{ $dueToday }}</strong></span>
            <span class="production-queue-kpi-inline__sep" aria-hidden="true">|</span>
            <span>{{ __('Machine') }} <strong>{{ $machine }}</strong></span>
            <span class="production-queue-kpi-inline__sep" aria-hidden="true">|</span>
            <span>{{ __('Operator') }} <strong>{{ $operator }}</strong></span>
            @if (filled($avgHours) && $avgHours !== '—')
                <span class="production-queue-kpi-inline__sep hidden xl:inline" aria-hidden="true">|</span>
                <span class="hidden xl:inline"><strong>{{ __('Avg') }}</strong> {{ $avgHours }}h</span>
            @endif
        </span>
    </div>
@else
    <div class="mb-3 grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9">
        @foreach ([
            ['label' => __('Waiting jobs'), 'value' => $waiting],
            ['label' => __('Running jobs'), 'value' => $running],
            ['label' => __('Paused jobs'), 'value' => $paused],
            ['label' => __('Completed today'), 'value' => $completed],
            ['label' => __('Overdue jobs'), 'value' => $overdue],
            ['label' => __('Due today'), 'value' => $dueToday],
            ['label' => __('Machine utilisation'), 'value' => $machine],
            ['label' => __('Operator utilisation'), 'value' => $operator],
            ['label' => __('Avg completion (h)'), 'value' => $avgHours ?? '—'],
        ] as $kpi)
            <div class="production-queue-kpi-card">
                <p class="production-queue-kpi-card__label">{{ $kpi['label'] }}</p>
                <p class="production-queue-kpi-card__value">{{ $kpi['value'] }}</p>
            </div>
        @endforeach
    </div>
@endif
