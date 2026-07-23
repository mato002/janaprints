<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(__('Access Audit Report')); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #64748b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 11px; text-transform: uppercase; }
        .risk-critical { color: #b91c1c; font-weight: bold; }
        .risk-high { color: #c2410c; font-weight: bold; }
    </style>
</head>
<body>
    <h1><?php echo e(__('Access Audit Report')); ?></h1>
    <p class="meta"><?php echo e(__('Generated')); ?>: <?php echo e($generatedAt->format('M j, Y g:i A')); ?> · <?php echo e(__('Records')); ?>: <?php echo e($events->count()); ?></p>

    <table>
        <thead>
            <tr>
                <th><?php echo e(__('Timestamp')); ?></th>
                <th><?php echo e(__('User')); ?></th>
                <th><?php echo e(__('Module')); ?></th>
                <th><?php echo e(__('Action')); ?></th>
                <th><?php echo e(__('Description')); ?></th>
                <th><?php echo e(__('IP')); ?></th>
                <th><?php echo e(__('Risk')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($event->occurred_at?->format('Y-m-d H:i')); ?></td>
                    <td><?php echo e($event->user?->name ?? '—'); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::headline($event->module)); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::headline($event->action)); ?></td>
                    <td><?php echo e($event->description); ?></td>
                    <td><?php echo e($event->ip_address ?? '—'); ?></td>
                    <td class="risk-<?php echo e($event->risk_level->value); ?>"><?php echo e($event->risk_level->label()); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\security\audit\exports\pdf.blade.php ENDPATH**/ ?>