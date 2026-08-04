<?php
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
?>

<?php if($compact): ?>
    <div class="production-queue-kpi-chips" role="group" aria-label="<?php echo e(__('Queue summary')); ?>">
        <?php $__currentLoopData = $chips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e($chip['url']); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'production-queue-kpi-chip',
                    'production-queue-kpi-chip--active' => $chip['active'],
                    'production-queue-kpi-chip--danger' => ($chip['danger'] ?? false) && ! $chip['active'],
                ]); ?>"
                data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
                data-turbo-action="advance"
            >
                <span class="production-queue-kpi-chip__label"><?php echo e($chip['label']); ?></span>
                <?php if($chip['count'] !== null): ?>
                    <span class="production-queue-kpi-chip__count"><?php echo e($chip['count']); ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <span class="production-queue-kpi-chips__meta" aria-label="<?php echo e(__('Utilisation')); ?>">
            <span><?php echo e(__('Due today')); ?> <strong><?php echo e($dueToday); ?></strong></span>
            <span class="production-queue-kpi-inline__sep" aria-hidden="true">|</span>
            <span><?php echo e(__('Machine')); ?> <strong><?php echo e($machine); ?></strong></span>
            <span class="production-queue-kpi-inline__sep" aria-hidden="true">|</span>
            <span><?php echo e(__('Operator')); ?> <strong><?php echo e($operator); ?></strong></span>
            <?php if(filled($avgHours) && $avgHours !== '—'): ?>
                <span class="production-queue-kpi-inline__sep hidden xl:inline" aria-hidden="true">|</span>
                <span class="hidden xl:inline"><strong><?php echo e(__('Avg')); ?></strong> <?php echo e($avgHours); ?>h</span>
            <?php endif; ?>
        </span>
    </div>
<?php else: ?>
    <div class="mb-3 grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9">
        <?php $__currentLoopData = [
            ['label' => __('Waiting jobs'), 'value' => $waiting],
            ['label' => __('Running jobs'), 'value' => $running],
            ['label' => __('Paused jobs'), 'value' => $paused],
            ['label' => __('Completed today'), 'value' => $completed],
            ['label' => __('Overdue jobs'), 'value' => $overdue],
            ['label' => __('Due today'), 'value' => $dueToday],
            ['label' => __('Machine utilisation'), 'value' => $machine],
            ['label' => __('Operator utilisation'), 'value' => $operator],
            ['label' => __('Avg completion (h)'), 'value' => $avgHours ?? '—'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="production-queue-kpi-card">
                <p class="production-queue-kpi-card__label"><?php echo e($kpi['label']); ?></p>
                <p class="production-queue-kpi-card__value"><?php echo e($kpi['value']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\metrics-strip.blade.php ENDPATH**/ ?>