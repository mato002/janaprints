<?php
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
?>

<div class="production-queue-ribbon sticky top-0 z-30 shrink-0">
    <?php if(count($departmentTabs) > 0): ?>
        <nav class="production-queue-ribbon__tabs" aria-label="<?php echo e(__('Departments')); ?>">
            <?php $__currentLoopData = $departmentTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(WorkspaceEmbed::url($tab['url'])); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'production-queue-ribbon__tab',
                        'production-queue-ribbon__tab--'.$tab['key'] => filled($tab['key'] ?? null),
                        'production-queue-ribbon__tab--active' => $tab['active'] ?? false,
                    ]); ?>"
                    data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
                    data-turbo-action="advance"
                ><?php echo e($tab['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    <?php endif; ?>

    <div class="production-queue-ribbon__stats" role="group" aria-label="<?php echo e(__('Queue summary')); ?>">
        <?php $__currentLoopData = $statFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($index > 0): ?>
                <span class="production-queue-ribbon__stat-sep" aria-hidden="true">•</span>
            <?php endif; ?>
            <a
                href="<?php echo e($stat['url']); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'production-queue-ribbon__stat',
                    'production-queue-ribbon__stat--active' => $stat['active'],
                    'production-queue-ribbon__stat--danger' => ($stat['danger'] ?? false) && ! $stat['active'],
                ]); ?>"
                data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
                data-turbo-action="advance"
            >
                <?php echo e($stat['label']); ?>

                <?php if($stat['count'] !== null): ?>
                    <strong class="tabular-nums"><?php echo e($stat['count']); ?></strong>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <span class="production-queue-ribbon__stat-sep hidden sm:inline" aria-hidden="true">•</span>
        <span class="production-queue-ribbon__stat-meta hidden sm:inline">
            <?php echo e(__('Due today')); ?> <strong class="tabular-nums"><?php echo e($dueToday); ?></strong>
        </span>
        <span class="production-queue-ribbon__stat-sep hidden md:inline" aria-hidden="true">•</span>
        <span class="production-queue-ribbon__stat-meta hidden md:inline">
            <?php echo e(__('Machine')); ?> <strong><?php echo e($machine); ?></strong>
        </span>
        <span class="production-queue-ribbon__stat-sep hidden lg:inline" aria-hidden="true">•</span>
        <span class="production-queue-ribbon__stat-meta hidden lg:inline">
            <?php echo e(__('Operator')); ?> <strong><?php echo e($operator); ?></strong>
        </span>
        <?php if(filled($avgHours) && $avgHours !== '—'): ?>
            <span class="production-queue-ribbon__stat-sep hidden xl:inline" aria-hidden="true">•</span>
            <span class="production-queue-ribbon__stat-meta hidden xl:inline">
                <?php echo e(__('Avg')); ?> <strong><?php echo e($avgHours); ?>h</strong>
            </span>
        <?php endif; ?>
    </div>

    <div class="production-queue-ribbon__filters">
        <?php echo $__env->make('admin.production.queue.partials.toolbar', [
            'indexRoute' => $indexRoute,
            'filters' => $filters,
            'workCenters' => $workCenters,
            'operators' => $operators,
            'machines' => $machines,
            'customers' => $customers,
            'workspace' => $workspace,
            'activeDepartment' => $activeDepartment,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/queue/partials/production-queue-ribbon.blade.php ENDPATH**/ ?>