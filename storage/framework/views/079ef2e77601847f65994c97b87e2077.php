<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(__('Attendance Register')); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1><?php echo e(__('Attendance Register')); ?></h1>
    <p class="meta"><?php echo e(__('Generated')); ?>: <?php echo e($generatedAt->format('Y-m-d H:i')); ?></p>

    <table>
        <thead>
            <tr>
                <th><?php echo e(__('Date')); ?></th>
                <th><?php echo e(__('Employee')); ?></th>
                <th><?php echo e(__('Employee Number')); ?></th>
                <th><?php echo e(__('Department')); ?></th>
                <th><?php echo e(__('Branch')); ?></th>
                <th><?php echo e(__('Shift')); ?></th>
                <th><?php echo e(__('Clock In')); ?></th>
                <th><?php echo e(__('Clock Out')); ?></th>
                <th><?php echo e(__('Hours')); ?></th>
                <th><?php echo e(__('Overtime')); ?></th>
                <th><?php echo e(__('Status')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($record->attendance_date?->format('Y-m-d')); ?></td>
                    <td><?php echo e($record->employee?->full_name); ?></td>
                    <td><?php echo e($record->employee?->employee_number); ?></td>
                    <td><?php echo e($record->department?->name); ?></td>
                    <td><?php echo e($record->branch?->name); ?></td>
                    <td><?php echo e($record->shift?->name); ?></td>
                    <td><?php echo e($record->clock_in_at?->format('H:i')); ?></td>
                    <td><?php echo e($record->clock_out_at?->format('H:i')); ?></td>
                    <td><?php echo e($record->actual_hours); ?></td>
                    <td><?php echo e($record->overtime_hours); ?></td>
                    <td><?php echo e($record->status?->label()); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\attendance\exports\pdf.blade.php ENDPATH**/ ?>