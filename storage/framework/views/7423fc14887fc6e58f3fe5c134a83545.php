<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <?php echo $__env->make('exports.pdf-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>
    <?php echo $__env->make('exports.partials.pdf-header', [
        'pdfLogoDataUri' => $pdfLogoDataUri ?? null,
        'pdfCompanyName' => $pdfCompanyName ?? null,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <h1><?php echo e($title); ?></h1>
    <?php if(! empty($subtitle)): ?>
        <p class="meta"><?php echo e($subtitle); ?></p>
    <?php endif; ?>
    <p class="meta"><?php echo e(__('Generated')); ?>: <?php echo e($generatedAt->format('Y-m-d H:i')); ?></p>
    <table>
        <?php if(! empty($headers)): ?>
            <thead>
                <tr>
                    <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th><?php echo e($header); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
        <?php endif; ?>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td><?php echo e($cell); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e(max(count($headers), 1)); ?>"><?php echo e(__('No rows to export.')); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\exports\tabular-pdf.blade.php ENDPATH**/ ?>