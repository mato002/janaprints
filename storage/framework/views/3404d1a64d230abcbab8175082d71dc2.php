<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9">
    <?php $__currentLoopData = [
        ['label' => __('Waiting jobs'), 'value' => $metrics['jobs_waiting'] ?? 0],
        ['label' => __('Running jobs'), 'value' => $metrics['jobs_running'] ?? 0],
        ['label' => __('Paused jobs'), 'value' => $metrics['jobs_paused'] ?? 0],
        ['label' => __('Completed today'), 'value' => $metrics['jobs_completed_today'] ?? 0],
        ['label' => __('Overdue jobs'), 'value' => $metrics['jobs_overdue'] ?? 0],
        ['label' => __('Due today'), 'value' => $metrics['jobs_due_today'] ?? 0],
        ['label' => __('Machine utilisation'), 'value' => isset($metrics['machine_utilisation_percent']) ? $metrics['machine_utilisation_percent'].'%' : '—'],
        ['label' => __('Operator utilisation'), 'value' => isset($metrics['operator_utilisation']) ? $metrics['operator_utilisation'].'%' : '—'],
        ['label' => __('Avg completion (h)'), 'value' => $metrics['average_completion_hours'] ?? '—'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="rounded-md border border-erp-border bg-white px-3 py-2">
            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500"><?php echo e($kpi['label']); ?></p>
            <p class="mt-0.5 text-lg font-semibold tabular-nums text-erp-primary"><?php echo e($kpi['value']); ?></p>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\metrics-strip.blade.php ENDPATH**/ ?>