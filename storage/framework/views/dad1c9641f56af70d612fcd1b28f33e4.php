<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(__('Leave Requests')); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1><?php echo e(__('Leave Requests')); ?></h1>
    <p><?php echo e(__('Generated')); ?>: <?php echo e($generatedAt->format('Y-m-d H:i')); ?></p>
    <table>
        <thead>
            <tr>
                <th><?php echo e(__('Reference')); ?></th>
                <th><?php echo e(__('Employee')); ?></th>
                <th><?php echo e(__('Type')); ?></th>
                <th><?php echo e(__('Start')); ?></th>
                <th><?php echo e(__('End')); ?></th>
                <th><?php echo e(__('Days')); ?></th>
                <th><?php echo e(__('Status')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($request->reference); ?></td>
                    <td><?php echo e($request->employee?->full_name); ?></td>
                    <td><?php echo e($request->leaveType?->name); ?></td>
                    <td><?php echo e($request->start_date?->format('Y-m-d')); ?></td>
                    <td><?php echo e($request->end_date?->format('Y-m-d')); ?></td>
                    <td><?php echo e($request->days_requested); ?></td>
                    <td><?php echo e($request->status?->label()); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\leave\exports\pdf.blade.php ENDPATH**/ ?>