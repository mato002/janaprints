<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?> — <?php echo e(config('app.name')); ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        p { margin: 0 0 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        .summary { display: flex; gap: 16px; margin-bottom: 16px; }
        .summary div { border: 1px solid #ddd; padding: 8px 12px; }
    </style>
</head>
<body onload="window.print()">
    <h1><?php echo e($active_register_label); ?></h1>
    <p><?php echo e($period_label); ?> · <?php echo e($branch_label); ?></p>

    <?php if(! empty($tab_data['summary'])): ?>
        <div class="summary">
            <?php $__currentLoopData = $tab_data['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><strong><?php echo e($item['label']); ?></strong><br><?php echo e($item['value']); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php echo $__env->make('admin.reports.operational-registers.partials.register-table', [
        'table' => $tab_data['table'] ?? [],
        'print' => true,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\operational-registers\print.blade.php ENDPATH**/ ?>