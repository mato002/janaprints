<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($title); ?> — <?php echo e(now()->format('Y-m-d')); ?></title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 12px; color: #1e293b; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { color: #64748b; margin-top: 0; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0; }
        .metric { border: 1px solid #e2e8f0; padding: 10px; border-radius: 6px; }
        .metric-label { font-size: 10px; text-transform: uppercase; color: #64748b; }
        .metric-value { font-size: 16px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #f8fafc; font-size: 10px; text-transform: uppercase; }
        h3 { font-size: 13px; margin: 20px 0 8px; }
    </style>
</head>
<body>
    <h1><?php echo e($title); ?></h1>
    <p><?php echo e($description); ?></p>
    <p><?php echo e(__('Period')); ?>: <?php echo e($filters['from_date']); ?> — <?php echo e($filters['to_date']); ?></p>

    <?php echo $__env->make('admin.reports.hr.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'print_mode' => true,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\hr\print.blade.php ENDPATH**/ ?>