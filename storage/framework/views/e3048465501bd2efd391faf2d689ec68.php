<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if($refreshSeconds ?? null): ?>
    <meta http-equiv="refresh" content="<?php echo e($refreshSeconds); ?>">
  <?php endif; ?>
    <title><?php echo e($sheet['job_number']); ?> — <?php echo e(__('Job sheet')); ?></title>
    <?php echo $__env->make('admin.production.job-cards.partials.job-sheet-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>
    <div class="no-print job-sheet-toolbar">
        <button type="button" onclick="window.print()"><?php echo e(__('Print')); ?></button>
    </div>

    <?php echo $__env->make('admin.production.job-cards.partials.job-sheet-body', ['sheet' => $sheet], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\floor-display.blade.php ENDPATH**/ ?>